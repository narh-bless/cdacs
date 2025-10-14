<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['organizer', 'attendees']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('start_date', [$request->date_from, $request->date_to]);
        }

        // Filter by upcoming/past events
        if ($request->has('upcoming')) {
            if ($request->upcoming) {
                $query->upcoming();
            } else {
                $query->past();
            }
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_date', 'asc')->paginate(15);

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $eventData = $request->validated();
        $eventData['organizer_id'] = auth()->id();

        $event = Event::create($eventData);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event->load('organizer')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): JsonResponse
    {
        return response()->json($event->load(['organizer', 'attendees']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        // Check if user can update this event
        if ($event->organizer_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->update($request->validated());

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event->load(['organizer', 'attendees'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): JsonResponse
    {
        // Check if user can delete this event
        if ($event->organizer_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * Register for an event.
     */
    public function register(Event $event): JsonResponse
    {
        $user = auth()->user();

        // Check if event requires registration
        if (!$event->requires_registration) {
            return response()->json(['message' => 'This event does not require registration'], 400);
        }

        // Check if event is full
        if ($event->isFull()) {
            return response()->json(['message' => 'This event is full'], 400);
        }

        // Check if user is already registered
        if ($event->attendees()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are already registered for this event'], 400);
        }

        $event->attendees()->attach($user->id, [
            'status' => 'registered',
            'notes' => null,
        ]);

        return response()->json([
            'message' => 'Successfully registered for the event',
            'event' => $event->load(['organizer', 'attendees'])
        ]);
    }

    /**
     * Unregister from an event.
     */
    public function unregister(Event $event): JsonResponse
    {
        $user = auth()->user();

        $event->attendees()->updateExistingPivot($user->id, [
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Successfully unregistered from the event',
            'event' => $event->load(['organizer', 'attendees'])
        ]);
    }

    /**
     * Mark attendance for an event.
     */
    public function markAttendance(Event $event, Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:attended,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $event->attendees()->updateExistingPivot($request->user_id, [
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Attendance marked successfully',
            'event' => $event->load(['organizer', 'attendees'])
        ]);
    }
}
