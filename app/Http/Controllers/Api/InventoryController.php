<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryMovement::with('product');
        
        // Apply date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Apply movement type filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        return $query->orderByDesc('created_at')->paginate(25);
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            if ($data['type'] === 'out' && $product->stock < $data['quantity']) {
                abort(422, 'Insufficient stock');
            }

            $data['type'] === 'in'
                ? $product->increment('stock', $data['quantity'])
                : $product->decrement('stock', $data['quantity']);

            $movement = InventoryMovement::create($data);
            return $movement->load('product');
        });
    }
}


