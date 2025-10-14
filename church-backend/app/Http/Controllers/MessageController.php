<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Models\Message;
use App\Models\MessageGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Message::with(['sender', 'recipient', 'parentMessage']);

        // Get messages for the authenticated user
        $query->where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('recipient_id', $user->id);
        });

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('unread')) {
            if ($request->unread) {
                $query->unread();
            } else {
                $query->read();
            }
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search by subject or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $messageData = $request->validated();
        $messageData['sender_id'] = auth()->id();

        $message = Message::create($messageData);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender', 'recipient'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message): JsonResponse
    {
        $user = auth()->user();

        // Check if user can view this message
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mark as read if user is the recipient
        if ($message->recipient_id === $user->id && !$message->is_read) {
            $message->markAsRead();
        }

        return response()->json($message->load(['sender', 'recipient', 'parentMessage', 'replies']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        // Check if user can update this message
        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update($request->validated());

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message->load(['sender', 'recipient'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message): JsonResponse
    {
        // Check if user can delete this message
        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): JsonResponse
    {
        $user = auth()->user();

        // Check if user is the recipient
        if ($message->recipient_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->markAsRead();

        return response()->json(['message' => 'Message marked as read']);
    }

    /**
     * Mark message as unread.
     */
    public function markAsUnread(Message $message): JsonResponse
    {
        $user = auth()->user();

        // Check if user is the recipient
        if ($message->recipient_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update([
            'is_read' => false,
            'read_at' => null,
        ]);

        return response()->json(['message' => 'Message marked as unread']);
    }

    /**
     * Reply to a message.
     */
    public function reply(Message $message, StoreMessageRequest $request): JsonResponse
    {
        $replyData = $request->validated();
        $replyData['sender_id'] = auth()->id();
        $replyData['parent_message_id'] = $message->id;
        $replyData['recipient_id'] = $message->sender_id;

        $reply = Message::create($replyData);

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => $reply->load(['sender', 'recipient', 'parentMessage'])
        ], 201);
    }

    /**
     * Get message groups.
     */
    public function groups(): JsonResponse
    {
        $user = auth()->user();
        $groups = MessageGroup::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('members')->get();

        return response()->json($groups);
    }

    /**
     * Create a message group.
     */
    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        $group = MessageGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        // Add members to the group
        $members = array_unique(array_merge($request->members, [auth()->id()]));
        $group->members()->attach($members);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group->load('members')
        ], 201);
    }

    /**
     * Send message to group.
     */
    public function sendToGroup(MessageGroup $group, StoreMessageRequest $request): JsonResponse
    {
        // Check if user is a member of the group
        if (!$group->members()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messageData = $request->validated();
        $messageData['sender_id'] = auth()->id();
        $messageData['type'] = 'group';

        $message = Message::create($messageData);

        // Attach message to group
        $message->groups()->attach($group->id);

        return response()->json([
            'message' => 'Message sent to group successfully',
            'data' => $message->load(['sender', 'groups'])
        ], 201);
    }
}
