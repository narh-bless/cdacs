<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContributionRequest;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Contribution;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    /**
     * Display a listing of contributions.
     */
    public function contributions(Request $request): JsonResponse
    {
        $query = Contribution::with(['user', 'recorder']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $contributions = $query->orderBy('contribution_date', 'desc')->paginate(15);

        return response()->json($contributions);
    }

    /**
     * Store a newly created contribution.
     */
    public function storeContribution(StoreContributionRequest $request): JsonResponse
    {
        $contributionData = $request->validated();
        $contributionData['recorded_by'] = auth()->id();

        $contribution = Contribution::create($contributionData);

        return response()->json([
            'message' => 'Contribution recorded successfully',
            'contribution' => $contribution->load(['user', 'recorder'])
        ], 201);
    }

    /**
     * Display a listing of donations.
     */
    public function donations(Request $request): JsonResponse
    {
        $query = Donation::with(['user', 'recorder']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by anonymous status
        if ($request->has('anonymous')) {
            $query->where('is_anonymous', $request->anonymous);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by donor name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_email', 'like', "%{$search}%");
            });
        }

        $donations = $query->orderBy('donation_date', 'desc')->paginate(15);

        return response()->json($donations);
    }

    /**
     * Store a newly created donation.
     */
    public function storeDonation(StoreDonationRequest $request): JsonResponse
    {
        $donationData = $request->validated();
        $donationData['recorded_by'] = auth()->id();

        $donation = Donation::create($donationData);

        return response()->json([
            'message' => 'Donation recorded successfully',
            'donation' => $donation->load(['user', 'recorder'])
        ], 201);
    }

    /**
     * Get financial summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        // Contributions summary
        $contributionsQuery = Contribution::confirmed()->dateRange($dateFrom, $dateTo);
        $contributionsByType = $contributionsQuery->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        $totalContributions = $contributionsQuery->sum('amount');

        // Donations summary
        $donationsQuery = Donation::confirmed()->dateRange($dateFrom, $dateTo);
        $donationsByType = $donationsQuery->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        $totalDonations = $donationsQuery->sum('amount');

        // Payment methods summary
        $paymentMethods = collect([
            'contributions' => $contributionsQuery->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get()
                ->pluck('total', 'payment_method'),
            'donations' => $donationsQuery->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get()
                ->pluck('total', 'payment_method'),
        ]);

        return response()->json([
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'contributions' => [
                'total' => $totalContributions,
                'by_type' => $contributionsByType,
                'count' => $contributionsQuery->count(),
            ],
            'donations' => [
                'total' => $totalDonations,
                'by_type' => $donationsByType,
                'count' => $donationsQuery->count(),
            ],
            'grand_total' => $totalContributions + $totalDonations,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Get user's financial history.
     */
    public function userHistory(Request $request): JsonResponse
    {
        $user = auth()->user();
        $dateFrom = $request->get('date_from', now()->subYear());
        $dateTo = $request->get('date_to', now());

        $contributions = $user->contributions()
            ->confirmed()
            ->dateRange($dateFrom, $dateTo)
            ->orderBy('contribution_date', 'desc')
            ->get();

        $donations = $user->donations()
            ->confirmed()
            ->dateRange($dateFrom, $dateTo)
            ->orderBy('donation_date', 'desc')
            ->get();

        return response()->json([
            'contributions' => $contributions,
            'donations' => $donations,
            'summary' => [
                'total_contributions' => $contributions->sum('amount'),
                'total_donations' => $donations->sum('amount'),
                'grand_total' => $contributions->sum('amount') + $donations->sum('amount'),
            ],
        ]);
    }

    /**
     * Update contribution status.
     */
    public function updateContributionStatus(Contribution $contribution, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $contribution->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Contribution status updated successfully',
            'contribution' => $contribution->load(['user', 'recorder'])
        ]);
    }

    /**
     * Update donation status.
     */
    public function updateDonationStatus(Donation $donation, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $donation->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Donation status updated successfully',
            'donation' => $donation->load(['user', 'recorder'])
        ]);
    }
}
