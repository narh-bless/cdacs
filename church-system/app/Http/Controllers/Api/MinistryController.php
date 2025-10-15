<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MinistryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ministry::with(['leader', 'members']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by leader
        if ($request->has('leader_id')) {
            $query->where('leader_id', $request->leader_id);
        }

        $ministries = $query->paginate($request->get('per_page', 15));

        return response()->json($ministries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ministries',
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ministry = Ministry::create($request->all());
        $ministry->load(['leader', 'members']);

        return response()->json([
            'message' => 'Ministry created successfully',
            'ministry' => $ministry
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ministry $ministry)
    {
        $ministry->load(['leader', 'members', 'contributions']);
        
        return response()->json($ministry);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ministry $ministry)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:ministries,name,' . $ministry->id,
            'description' => 'nullable|string',
            'leader_id' => 'sometimes|required|exists:users,id',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ministry->update($request->all());
        $ministry->load(['leader', 'members']);

        return response()->json([
            'message' => 'Ministry updated successfully',
            'ministry' => $ministry
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ministry $ministry)
    {
        $ministry->delete();

        return response()->json([
            'message' => 'Ministry deleted successfully'
        ]);
    }

    /**
     * Get members of a specific ministry.
     */
    public function members(Ministry $ministry)
    {
        $members = $ministry->members()
            ->withPivot('role', 'joined_date', 'is_active')
            ->orderBy('name')
            ->get();

        return response()->json($members);
    }

    /**
     * Add a member to a ministry.
     */
    public function addMember(Request $request, Ministry $ministry)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:100',
            'joined_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        // Check if user is already a member
        if ($ministry->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is already a member of this ministry'
            ], 400);
        }

        $pivotData = [
            'role' => $request->get('role', 'member'),
            'joined_date' => $request->get('joined_date', now()),
            'is_active' => $request->get('is_active', true),
        ];

        $ministry->members()->attach($user->id, $pivotData);
        $ministry->load('members');

        return response()->json([
            'message' => 'Member added successfully',
            'ministry' => $ministry
        ]);
    }

    /**
     * Remove a member from a ministry.
     */
    public function removeMember(Ministry $ministry, User $user)
    {
        if (!$ministry->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is not a member of this ministry'
            ], 400);
        }

        $ministry->members()->detach($user->id);
        $ministry->load('members');

        return response()->json([
            'message' => 'Member removed successfully',
            'ministry' => $ministry
        ]);
    }

    /**
     * Get contributions for a specific ministry.
     */
    public function contributions(Ministry $ministry)
    {
        $contributions = $ministry->contributions()
            ->with(['user', 'recordedBy'])
            ->orderBy('contribution_date', 'desc')
            ->paginate(15);

        return response()->json($contributions);
    }
}
