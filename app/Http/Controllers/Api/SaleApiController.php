<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SaleApiController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(): JsonResponse
    {
        $sales = Sale::with(['user', 'products'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['user', 'products']);

        return response()->json([
            'success' => true,
            'data' => $sale
        ]);
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $sale = Sale::create($validated);

        return response()->json([
            'success' => true,
            'data' => $sale,
            'message' => 'Sale created successfully'
        ], 201);
    }

    /**
     * Update the specified sale
     */
    public function update(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'total_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|in:pending,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'data' => $sale,
            'message' => 'Sale updated successfully'
        ]);
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Sale $sale): JsonResponse
    {
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully'
        ]);
    }
} 