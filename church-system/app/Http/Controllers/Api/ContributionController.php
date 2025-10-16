<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contribution::with(['user', 'ministry', 'recordedBy']);

        // Filter by contribution type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by ministry
        if ($request->has('ministry_id')) {
            $query->where('ministry_id', $request->ministry_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filtering
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('contribution_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->has('start_date')) {
            $query->where('contribution_date', '>=', $request->start_date);
        } elseif ($request->has('end_date')) {
            $query->where('contribution_date', '<=', $request->end_date);
        }

        // Search by user name or notes
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Order by contribution date (most recent first)
        $query->orderBy('contribution_date', 'desc');

        $contributions = $query->paginate($request->get('per_page', 15));

        return response()->json($contributions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:tithe,offering,donation,special',
            'payment_method' => 'required|in:cash,bank_transfer,card,check,mobile_money',
            'contribution_date' => 'required|date',
            'ministry_id' => 'nullable|exists:ministries,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contributionData = $request->all();
        $contributionData['recorded_by'] = auth()->id();

        $contribution = Contribution::create($contributionData);
        $contribution->load(['user', 'ministry', 'recordedBy']);

        return response()->json([
            'message' => 'Contribution recorded successfully',
            'data' => $contribution
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contribution $contribution)
    {
        $contribution->load(['user', 'ministry', 'recordedBy']);
        
        return response()->json($contribution);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contribution $contribution)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|exists:users,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'type' => 'sometimes|required|in:tithe,offering,donation,special',
            'payment_method' => 'sometimes|required|in:cash,bank_transfer,card,check,mobile_money',
            'contribution_date' => 'sometimes|required|date',
            'ministry_id' => 'nullable|exists:ministries,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contribution->update($request->all());
        $contribution->load(['user', 'ministry', 'recordedBy']);

        return response()->json([
            'message' => 'Contribution updated successfully',
            'data' => $contribution
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contribution $contribution)
    {
        $contribution->delete();

        return response()->json([
            'message' => 'Contribution deleted successfully'
        ]);
    }

    /**
     * Get contribution summary report.
     */
    public function summary(Request $request)
    {
        $query = Contribution::query();

        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('contribution_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $summary = [
            'total' => $query->sum('amount'),
            'tithe' => Contribution::where('type', 'tithe')->sum('amount'),
            'offering' => Contribution::where('type', 'offering')->sum('amount'),
            'donation' => Contribution::where('type', 'donation')->sum('amount'),
            'special' => Contribution::where('type', 'special')->sum('amount'),
            'total_count' => $query->count(),
            'contributors_count' => $query->distinct('user_id')->count('user_id'),
        ];

        return response()->json([
            'data' => $summary
        ]);
    }

    /**
     * Get contributions grouped by type.
     */
    public function byType(Request $request)
    {
        $query = Contribution::query();

        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('contribution_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $byType = $query->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'data' => $byType
        ]);
    }

    /**
     * Get contributions grouped by ministry.
     */
    public function byMinistry(Request $request)
    {
        $query = Contribution::query();

        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('contribution_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $byMinistry = $query->whereNotNull('ministry_id')
            ->select('ministry_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->with('ministry:id,name')
            ->groupBy('ministry_id')
            ->get()
            ->map(function ($item) {
                return [
                    'ministry_id' => $item->ministry_id,
                    'ministry_name' => $item->ministry->name ?? 'Unknown',
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'data' => $byMinistry
        ]);
    }

    /**
     * Get contributions by date range.
     */
    public function byDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contributions = Contribution::with(['user', 'ministry', 'recordedBy'])
            ->whereBetween('contribution_date', [
                $request->start_date,
                $request->end_date
            ])
            ->orderBy('contribution_date', 'desc')
            ->get();

        $summary = [
            'total' => $contributions->sum('amount'),
            'count' => $contributions->count(),
            'contributions' => $contributions,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        return response()->json([
            'data' => $summary
        ]);
    }
}
