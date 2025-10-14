<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with('author');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by published status
        if ($request->has('published')) {
            if ($request->published) {
                $query->published();
            } else {
                $query->where('is_published', false);
            }
        } else {
            // Default to published announcements for non-admin users
            if (!auth()->user()->hasRole('administrator')) {
                $query->published();
            }
        }

        // Search by title or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($announcements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $announcementData = $request->validated();
        $announcementData['author_id'] = auth()->id();

        $announcement = Announcement::create($announcementData);

        return response()->json([
            'message' => 'Announcement created successfully',
            'announcement' => $announcement->load('author')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json($announcement->load('author'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        // Check if user can update this announcement
        if ($announcement->author_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $announcement->update($request->validated());

        return response()->json([
            'message' => 'Announcement updated successfully',
            'announcement' => $announcement->load('author')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        // Check if user can delete this announcement
        if ($announcement->author_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }

    /**
     * Publish an announcement.
     */
    public function publish(Announcement $announcement): JsonResponse
    {
        if (!$announcement->is_published) {
            $announcement->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Announcement published successfully',
            'announcement' => $announcement->load('author')
        ]);
    }

    /**
     * Unpublish an announcement.
     */
    public function unpublish(Announcement $announcement): JsonResponse
    {
        $announcement->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return response()->json([
            'message' => 'Announcement unpublished successfully',
            'announcement' => $announcement->load('author')
        ]);
    }
}
