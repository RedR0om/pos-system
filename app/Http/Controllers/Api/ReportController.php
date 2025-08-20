<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function salesSummary(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Sale::query()->where('status', 'paid');
        if (!empty($data['from'])) {
            $query->whereDate('paid_at', '>=', $data['from']);
        }
        if (!empty($data['to'])) {
            $query->whereDate('paid_at', '<=', $data['to']);
        }

        return [
            'count' => (clone $query)->count(),
            'total' => (clone $query)->sum('total'),
            'subtotal' => (clone $query)->sum('subtotal'),
        ];
    }

    public function salesByDay()
    {
        $from = now()->subDays(6)->startOfDay();
        $to = now()->endOfDay();
        $rows = \App\Models\Sale::selectRaw('DATE(created_at) as d, SUM(total) as t')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'paid')
            ->groupBy('d')
            ->orderBy('d')
            ->get();
        $map = collect();
        for ($i = 0; $i < 7; $i++) { $map[ $from->copy()->addDays($i)->toDateString() ] = 0; }
        foreach ($rows as $r) { $map[$r->d] = (float) $r->t; }
        return [ 'labels' => array_keys($map->all()), 'data' => array_values($map->all()) ];
    }

    public function salesByCategory()
    {
        $from = now()->subDays(30);
        $rows = \App\Models\SaleItem::query()
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as category, SUM(sale_items.total) as t')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid')
            ->where('sales.created_at', '>=', $from)
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        return [ 'labels' => $rows->pluck('category'), 'data' => $rows->pluck('t')->map(fn($v) => (float)$v) ];
    }

    public function dashboardSummary()
    {
        $today = now()->toDateString();
        $thirtyDaysAgo = now()->subDays(30);
        $sevenDaysAgo = now()->subDays(7);

        $totalProducts = \App\Models\Product::count();
        $lowStock = \App\Models\Product::where('stock', '<=', 5)->count();
        $totalCategories = \App\Models\Category::count();
        $stockOnHand = (int) \App\Models\Product::sum('stock');
        $stockValue = (float) \App\Models\Product::selectRaw('SUM(stock * price) as v')->value('v') ?? 0;

        $paidSalesTotal = (float) \App\Models\Sale::where('status', 'paid')->sum('total');
        $todaySalesCount = (int) \App\Models\Sale::where('status', 'paid')->whereDate('paid_at', $today)->count();
        $todaySalesTotal = (float) \App\Models\Sale::where('status', 'paid')->whereDate('paid_at', $today)->sum('total');

        $inventoryIn = (int) \App\Models\InventoryMovement::where('type', 'in')->where('created_at', '>=', $sevenDaysAgo)->count();
        $inventoryOut = (int) \App\Models\InventoryMovement::where('type', 'out')->where('created_at', '>=', $sevenDaysAgo)->count();

        $recentSales = \App\Models\Sale::where('status', 'paid')
            ->with(['items.product:id,name'])
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get(['id','total','paid_at']);

        $topProducts = \App\Models\SaleItem::selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.total) as total')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid')
            ->where('sales.created_at', '>=', $thirtyDaysAgo)
            ->groupBy('products.name')
            ->orderByDesc(\DB::raw('SUM(sale_items.quantity)'))
            ->limit(5)
            ->get();

        return [
            'inventory' => [
                'total_products' => $totalProducts,
                'low_stock' => $lowStock,
                'total_categories' => $totalCategories,
                'stock_on_hand' => $stockOnHand,
                'stock_value' => $stockValue,
                'movements_last7' => [ 'in' => $inventoryIn, 'out' => $inventoryOut ],
            ],
            'sales' => [
                'paid_total' => $paidSalesTotal,
                'today' => [ 'count' => $todaySalesCount, 'total' => $todaySalesTotal ],
                'recent' => $recentSales,
                'top_products' => $topProducts,
            ],
        ];
    }
}


