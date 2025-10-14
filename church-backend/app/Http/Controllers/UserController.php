<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by membership status
        if ($request->has('membership_status')) {
            $query->where('membership_status', $request->membership_status);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $userData = $request->validated();
        $userData['password'] = Hash::make($userData['password']);
        
        $user = User::create($userData);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles', 'ministries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $userData = $request->validated();

        // Hash password if provided
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user->update($userData);

        // Update roles if provided
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get user's contributions.
     */
    public function contributions(User $user, Request $request): JsonResponse
    {
        $query = $user->contributions();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $contributions = $query->orderBy('contribution_date', 'desc')->paginate(15);

        return response()->json($contributions);
    }

    /**
     * Get user's donations.
     */
    public function donations(User $user, Request $request): JsonResponse
    {
        $query = $user->donations();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $donations = $query->orderBy('donation_date', 'desc')->paginate(15);

        return response()->json($donations);
    }

    /**
     * Get user's profile.
     */
    public function profile(): JsonResponse
    {
        $user = auth()->user();
        return response()->json($user->load('roles', 'ministries'));
    }

    /**
     * Update user's profile.
     */
    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $userData = $request->validated();

        // Remove sensitive fields that users shouldn't update themselves
        unset($userData['roles'], $userData['membership_status'], $userData['is_active']);

        // Hash password if provided
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user->update($userData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('roles', 'ministries')
        ]);
    }
}
