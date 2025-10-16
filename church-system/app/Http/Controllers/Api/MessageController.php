<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Ministry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display all messages (combined inbox and sent).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Message::with(['sender', 'recipient', 'ministry'])
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('recipient_id', $userId);
            });

        // Filter by message type
        if ($request->has('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        // Search by subject or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $messages = $query->paginate($request->get('per_page', 15));

        return response()->json($messages);
    }

    /**
     * Get inbox messages (received messages).
     */
    public function inbox(Request $request)
    {
        $userId = Auth::id();
        $query = Message::with(['sender', 'recipient', 'ministry'])
            ->where('recipient_id', $userId);

        // Filter by message type
        if ($request->has('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        // Search by subject or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Order by unread first, then latest
        $query->orderBy('is_read', 'asc')
              ->orderBy('created_at', 'desc');

        $messages = $query->paginate($request->get('per_page', 15));

        return response()->json($messages);
    }

    /**
     * Get sent messages.
     */
    public function sent(Request $request)
    {
        $userId = Auth::id();
        $query = Message::with(['sender', 'recipient', 'ministry'])
            ->where('sender_id', $userId);

        // Filter by message type
        if ($request->has('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        // Search by subject or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $messages = $query->paginate($request->get('per_page', 15));

        return response()->json($messages);
    }

    /**
     * Store a new message (personal or ministry).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'message_type' => 'required|in:personal,ministry,broadcast',
            'recipient_id' => 'required_if:message_type,personal|exists:users,id',
            'ministry_id' => 'required_if:message_type,ministry|exists:ministries,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $messageData = [
            'sender_id' => Auth::id(),
            'subject' => $request->subject,
            'content' => $request->content,
            'message_type' => $request->message_type,
        ];

        // Personal message
        if ($request->message_type === 'personal') {
            $messageData['recipient_id'] = $request->recipient_id;
            $message = Message::create($messageData);
            $message->load(['sender', 'recipient']);
            
            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);
        }

        // Ministry message - send to all ministry members
        if ($request->message_type === 'ministry') {
            $ministry = Ministry::with('members')->findOrFail($request->ministry_id);
            $messages = [];

            foreach ($ministry->members as $member) {
                // Don't send to sender
                if ($member->id !== Auth::id()) {
                    $messages[] = Message::create([
                        'sender_id' => Auth::id(),
                        'recipient_id' => $member->id,
                        'ministry_id' => $request->ministry_id,
                        'subject' => $request->subject,
                        'content' => $request->content,
                        'message_type' => 'ministry',
                    ]);
                }
            }

            return response()->json([
                'message' => 'Ministry message sent to ' . count($messages) . ' members',
                'data' => [
                    'subject' => $request->subject,
                    'ministry' => $ministry->name,
                    'recipients_count' => count($messages)
                ]
            ], 201);
        }

        return response()->json(['error' => 'Invalid message type'], 400);
    }

    /**
     * Send a broadcast message to all users.
     */
    public function broadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get all users except the sender
        $users = User::where('id', '!=', Auth::id())->get();
        $messages = [];

        foreach ($users as $user) {
            $messages[] = Message::create([
                'sender_id' => Auth::id(),
                'recipient_id' => $user->id,
                'subject' => $request->subject,
                'content' => $request->content,
                'message_type' => 'broadcast',
            ]);
        }

        return response()->json([
            'message' => 'Broadcast message sent to ' . count($messages) . ' users',
            'data' => [
                'subject' => $request->subject,
                'recipients_count' => count($messages)
            ]
        ], 201);
    }

    /**
     * Display a specific message.
     */
    public function show(Message $message)
    {
        // Ensure user has access to this message
        $userId = Auth::id();
        if ($message->sender_id !== $userId && $message->recipient_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->load(['sender', 'recipient', 'ministry']);

        // Auto-mark as read if user is recipient and message is unread
        if ($message->recipient_id === $userId && !$message->is_read) {
            $message->markAsRead();
        }

        return response()->json($message);
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(Message $message)
    {
        // Ensure user is the recipient
        if ($message->recipient_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($message->is_read) {
            return response()->json([
                'message' => 'Message already marked as read',
                'data' => $message
            ]);
        }

        $message->markAsRead();

        return response()->json([
            'message' => 'Message marked as read',
            'data' => $message
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy(Message $message)
    {
        // Ensure user has access to this message (sender or recipient)
        $userId = Auth::id();
        if ($message->sender_id !== $userId && $message->recipient_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully'
        ]);
    }
}
