<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Announcement::with('author');

        // Filter by published status
        if ($request->has('is_published')) {
            $query->where('is_published', $request->is_published);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search by title or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $announcements = $query->paginate($request->get('per_page', 15));

        return response()->json($announcements);
    }

    /**
     * Get published announcements.
     */
    public function published(Request $request)
    {
        $announcements = Announcement::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($announcements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'nullable|in:low,medium,high',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcementData = $request->all();
        $announcementData['author_id'] = auth()->id();

        // If publishing, set published_at if not provided
        if ($request->is_published && !$request->published_at) {
            $announcementData['published_at'] = now();
        }

        $announcement = Announcement::create($announcementData);
        $announcement->load('author');

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => $announcement
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $announcement->load('author');
        
        return response()->json($announcement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'priority' => 'nullable|in:low,medium,high',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement->update($request->all());
        $announcement->load('author');

        return response()->json([
            'message' => 'Announcement updated successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Publish an announcement.
     */
    public function publish(Announcement $announcement)
    {
        $announcement->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $announcement->load('author');

        return response()->json([
            'message' => 'Announcement published successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully'
        ]);
    }
}
