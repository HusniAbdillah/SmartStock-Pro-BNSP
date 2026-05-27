<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        return response()->json([
            'product_id'   => (int) $validated['product_id'],
            'warehouse_id' => (int) $validated['warehouse_id'],
            'quantity'     => $product->stockAtWarehouse((int) $validated['warehouse_id']),
        ]);
    }
}
