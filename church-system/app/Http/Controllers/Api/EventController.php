<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with('organizer');

        // Filter by published status
        if ($request->has('is_published')) {
            $query->where('is_published', $request->is_published);
        }

        // Filter by event type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Time-based filtering
        if ($request->has('time_filter')) {
            switch ($request->time_filter) {
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
                case 'past':
                    $query->where('end_date', '<', now());
                    break;
                case 'this_week':
                    $query->whereBetween('start_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereBetween('start_date', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]);
                    break;
            }
        }

        // Search by title, description, or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Order by start date (upcoming first)
        $query->orderBy('start_date', 'asc');

        $events = $query->paginate($request->get('per_page', 15));

        return response()->json($events);
    }

    /**
     * Get upcoming events.
     */
    public function upcoming(Request $request)
    {
        $events = Event::upcoming()
            ->published()
            ->with('organizer')
            ->orderBy('start_date', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json($events);
    }

    /**
     * Get published events.
     */
    public function published(Request $request)
    {
        $events = Event::published()
            ->with('organizer')
            ->orderBy('start_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|in:meeting,service,conference,workshop,prayer_meeting,bible_study,fellowship,outreach,celebration,other',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,biweekly,monthly,yearly',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $eventData = $request->all();
        $eventData['organizer_id'] = auth()->id();

        $event = Event::create($eventData);
        $event->load('organizer');

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('organizer');
        
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|in:meeting,service,conference,workshop,prayer_meeting,bible_study,fellowship,outreach,celebration,other',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,biweekly,monthly,yearly',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());
        $event->load('organizer');

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    /**
     * Publish an event.
     */
    public function publish(Event $event)
    {
        $event->update([
            'is_published' => true,
        ]);

        $event->load('organizer');

        return response()->json([
            'message' => 'Event published successfully',
            'data' => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
