<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::with(['items.product', 'payments', 'user'])
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments', 'in:cash,card,mobile,other'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
        ]);

        $userId = $request->user() ? $request->user()->id : null;

        return DB::transaction(function () use ($data, $userId) {
            $subtotal = collect($data['items'])->sum(function ($item) {
                return $item['unit_price'] * $item['quantity'];
            });
            $total = $subtotal;

            $sale = Sale::create([
                'user_id' => $userId,
                'customer_name' => $data['customer_name'] ?? null,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $line) {
                $product = Product::lockForUpdate()->findOrFail($line['product_id']);
                if ($product->stock < $line['quantity']) {
                    abort(422, 'Insufficient stock for product: ' . $product->name);
                }
                $product->decrement('stock', $line['quantity']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['unit_price'] * $line['quantity'],
                ]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reason' => 'sale',
                    'sale_id' => $sale->id,
                ]);
            }

            $paidAmount = 0;
            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $p) {
                    $paidAmount += $p['amount'];
                    Payment::create([
                        'sale_id' => $sale->id,
                        'method' => $p['method'],
                        'amount' => $p['amount'],
                        'change_due' => 0,
                        'reference' => $p['reference'] ?? null,
                    ]);
                }
            }

            if ($paidAmount >= $total) {
                $sale->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            return $sale->load(['items.product', 'payments']);
        });
    }

    public function show(Sale $sale)
    {
        return $sale->load(['items.product', 'payments', 'user']);
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'method' => ['required', 'in:cash,card,mobile,other'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string'],
        ]);

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'method' => $data['method'],
            'amount' => $data['amount'],
            'change_due' => 0,
            'reference' => $data['reference'] ?? null,
        ]);

        $paid = $sale->payments()->sum('amount') + $data['amount'];
        if ($paid >= $sale->total) {
            $sale->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return $payment;
    }
}


