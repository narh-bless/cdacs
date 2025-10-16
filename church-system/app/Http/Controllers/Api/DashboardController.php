<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Ministry;
use App\Models\Contribution;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get administrator dashboard statistics.
     */
    public function adminStats()
    {
        $stats = [
            // User statistics
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'users_this_month' => User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // Event statistics
            'total_events' => Event::count(),
            'upcoming_events' => Event::where('start_date', '>=', Carbon::now())->count(),
            'events_this_month' => Event::whereMonth('start_date', Carbon::now()->month)
                ->whereYear('start_date', Carbon::now()->year)
                ->count(),
            
            // Ministry statistics
            'total_ministries' => Ministry::count(),
            'active_ministries' => Ministry::where('is_active', true)->count(),
            
            // Announcement statistics
            'total_announcements' => Announcement::count(),
            'published_announcements' => Announcement::where('is_published', true)->count(),
            'announcements_this_month' => Announcement::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // Message statistics
            'total_messages' => Message::count(),
            'messages_this_month' => Message::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // User roles distribution
            'user_roles' => DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name', 'roles.id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->name => $item->count];
                }),
            
            // Recent users
            'recent_users' => User::with('roles:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->select('id', 'name', 'email', 'is_active', 'created_at')
                ->get()
                ->map(function ($user) {
                    $user->role = $user->roles->first()->name ?? 'user';
                    unset($user->roles);
                    return $user;
                }),
            
            // Monthly overview
            'monthly_overview' => [
                'new_members' => User::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                'events_held' => Event::whereMonth('start_date', Carbon::now()->month)
                    ->whereYear('start_date', Carbon::now()->year)
                    ->where('start_date', '<', Carbon::now())
                    ->count(),
                'announcements' => Announcement::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                'messages_sent' => Message::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
            ],
        ];

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get pastor dashboard statistics.
     */
    public function pastorStats()
    {
        $stats = [
            // Member statistics
            'total_members' => User::count(),
            'active_members' => User::where('is_active', true)->count(),
            
            // Announcement statistics
            'total_announcements' => Announcement::count(),
            'published_announcements' => Announcement::where('is_published', true)->count(),
            'announcements_this_month' => Announcement::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // Event statistics
            'upcoming_events' => Event::where('start_date', '>=', Carbon::now())->count(),
            'total_events' => Event::count(),
            'events_this_month' => Event::whereMonth('start_date', Carbon::now()->month)
                ->whereYear('start_date', Carbon::now()->year)
                ->count(),
            
            // Ministry statistics
            'total_ministries' => Ministry::count(),
            'active_ministries' => Ministry::where('is_active', true)->count(),
            
            // Recent announcements
            'recent_announcements' => Announcement::orderBy('created_at', 'desc')
                ->limit(5)
                ->select('id', 'title', 'priority', 'is_published', 'created_at')
                ->get(),
            
            // Upcoming events
            'upcoming_events_list' => Event::where('start_date', '>=', Carbon::now())
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->select('id', 'title', 'type', 'start_date', 'location')
                ->get(),
            
            // Active ministries
            'active_ministries_list' => Ministry::where('is_active', true)
                ->withCount('members')
                ->limit(6)
                ->select('id', 'name', 'description')
                ->get(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get finance committee dashboard statistics.
     */
    public function financeStats()
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $stats = [
            // Contribution totals
            'total_contributions' => Contribution::whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->sum('amount'),
            
            'tithes' => Contribution::where('type', 'tithe')
                ->whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->sum('amount'),
            
            'offerings' => Contribution::where('type', 'offering')
                ->whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->sum('amount'),
            
            'donations' => Contribution::where('type', 'donation')
                ->whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->sum('amount'),
            
            'special' => Contribution::where('type', 'special')
                ->whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->sum('amount'),
            
            // Contributor statistics
            'total_contributors' => Contribution::whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->distinct('user_id')
                ->count('user_id'),
            
            'total_contribution_count' => Contribution::whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->count(),
            
            // Average contribution
            'average_contribution' => Contribution::whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->avg('amount'),
            
            // Recent contributions
            'recent_contributions' => Contribution::with('user:id,name')
                ->orderBy('contribution_date', 'desc')
                ->limit(5)
                ->select('id', 'user_id', 'type', 'amount', 'payment_method', 'contribution_date')
                ->get(),
            
            // Contributions by type
            'contributions_by_type' => Contribution::whereMonth('contribution_date', $thisMonth)
                ->whereYear('contribution_date', $thisYear)
                ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get user dashboard statistics.
     */
    public function userStats(Request $request)
    {
        $userId = $request->user()->id;

        $stats = [
            // Personal contribution statistics
            'total_contributions' => Contribution::where('user_id', $userId)->count(),
            'total_contribution_amount' => Contribution::where('user_id', $userId)->sum('amount'),
            'contributions_this_year' => Contribution::where('user_id', $userId)
                ->whereYear('contribution_date', Carbon::now()->year)
                ->sum('amount'),
            
            // Ministry membership
            'total_ministries' => DB::table('user_ministries')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->count(),
            
            'ministries' => Ministry::whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('user_ministries.is_active', true);
            })
                ->select('id', 'name', 'description')
                ->limit(5)
                ->get(),
            
            // Message statistics
            'unread_messages' => Message::where('recipient_id', $userId)
                ->where('is_read', false)
                ->count(),
            
            'total_messages' => Message::where('recipient_id', $userId)->count(),
            
            // Event statistics
            'upcoming_events' => Event::where('start_date', '>=', Carbon::now())
                ->where('is_published', true)
                ->count(),
            
            'upcoming_events_list' => Event::where('start_date', '>=', Carbon::now())
                ->where('is_published', true)
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->select('id', 'title', 'type', 'start_date', 'location')
                ->get(),
            
            // Recent announcements
            'recent_announcements' => Announcement::where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->select('id', 'title', 'content', 'priority', 'published_at')
                ->get(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}

