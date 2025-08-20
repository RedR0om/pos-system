<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $query->whereDate('created_at', '>=', $data['from']);
        }
        if (!empty($data['to'])) {
            $query->whereDate('created_at', '<=', $data['to']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'count' => (clone $query)->count(),
                'total' => (float) ((clone $query)->sum('total') ?? 0),
                'subtotal' => (float) ((clone $query)->sum('subtotal') ?? 0),
            ]
        ]);
    }

    public function salesByDay(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        // Default to last 7 days if no dates provided
        if (!$fromDate && !$toDate) {
            $from = now()->subDays(6)->startOfDay();
            $to = now()->endOfDay();
        } else {
            $from = $fromDate ? \Carbon\Carbon::parse($fromDate)->startOfDay() : now()->subDays(6)->startOfDay();
            $to = $toDate ? \Carbon\Carbon::parse($toDate)->endOfDay() : now()->endOfDay();
        }
        
        $rows = \App\Models\Sale::selectRaw('DATE(created_at) as d, SUM(total) as t')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'paid')
            ->groupBy('d')
            ->orderBy('d')
            ->get();
            
        // Generate date range for the selected period
        $dateRange = collect();
        $currentDate = $from->copy();
        while ($currentDate <= $to) {
            $dateRange[$currentDate->toDateString()] = 0;
            $currentDate->addDay();
        }
        
        // Fill in actual sales data
        foreach ($rows as $r) {
            $dateRange[$r->d] = (float) $r->t;
        }
        
        return response()->json([
            'success' => true,
            'data' => $dateRange->map(function($amount, $date) {
                return [
                    'date' => $date,
                    'amount' => (float) $amount
                ];
            })->values()
        ]);
    }

    public function salesByCategory(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        // Default to last 30 days if no dates provided
        if (!$fromDate && !$toDate) {
            $from = now()->subDays(30);
        } else {
            $from = $fromDate ? \Carbon\Carbon::parse($fromDate)->startOfDay() : now()->subDays(30);
        }
        
        $query = \App\Models\SaleItem::query()
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as category, SUM(sale_items.total) as t')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid');
            
        if ($fromDate) {
            $query->whereDate('sales.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('sales.created_at', '<=', $toDate);
        }
        
        $rows = $query->groupBy('category')
            ->orderBy('category')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $rows->map(function($row) {
                return [
                    'category' => $row->category,
                    'amount' => (float) ($row->t ?? 0)
                ];
            })
        ]);
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
        $paidSalesCount = (int) \App\Models\Sale::where('status', 'paid')->count();
        $paidItemsCount = (int) \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid')
            ->sum('sale_items.quantity');
        $todaySalesCount = (int) \App\Models\Sale::where('status', 'paid')->whereDate('created_at', $today)->count();
        $todaySalesTotal = (float) \App\Models\Sale::where('status', 'paid')->whereDate('created_at', $today)->sum('total');

        $inventoryIn = (int) \App\Models\InventoryMovement::where('type', 'in')->where('created_at', '>=', $sevenDaysAgo)->count();
        $inventoryOut = (int) \App\Models\InventoryMovement::where('type', 'out')->where('created_at', '>=', $sevenDaysAgo)->count();

        $recentSales = \App\Models\Sale::where('status', 'paid')
            ->with(['items.product:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id','total','created_at']);

        $topProducts = \App\Models\SaleItem::selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.total) as total')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid')
            ->where('sales.created_at', '>=', $thirtyDaysAgo)
            ->groupBy('products.name')
            ->orderByDesc(\DB::raw('SUM(sale_items.quantity)'))
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'qty' => (int) ($item->qty ?? 0),
                    'total' => (float) ($item->total ?? 0)
                ];
            });

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
                'paid_count' => $paidSalesCount,
                'paid_items_count' => $paidItemsCount,
                'today' => [ 'count' => $todaySalesCount, 'total' => $todaySalesTotal ],
                'recent' => $recentSales,
                'top_products' => $topProducts,
            ],
        ];
    }

    public function topProducts(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $limit = $request->get('limit', 5);

        $query = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'paid');

        if ($fromDate) {
            $query->whereDate('sales.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('sales.created_at', '<=', $toDate);
        }

        $topProducts = $query->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as quantity'),
                DB::raw('SUM(sale_items.total) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'quantity' => (int) ($item->quantity ?? 0),
                    'revenue' => (float) ($item->revenue ?? 0)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topProducts
        ]);
    }

    public function recentSales(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $limit = $request->get('limit', 5);

        $query = Sale::where('status', 'paid');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $recentSales = $query->select('id', 'total', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'total' => (float) ($sale->total ?? 0),
                    'created_at' => $sale->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recentSales
        ]);
    }

    public function export(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Sale::where('status', 'paid');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $sales = $query->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV content
        $csvContent = $this->generateCsvContent($sales);
        
        // Return CSV file with proper headers
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="sales-report-' . ($fromDate ?? 'all') . '-to-' . ($toDate ?? 'all') . '.csv"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function inventoryExport(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $type = $request->get('type');

        $query = \App\Models\InventoryMovement::with('product');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV content
        $csvContent = $this->generateInventoryCsvContent($movements);
        
        // Return CSV file with proper headers
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="inventory-report-' . ($fromDate ?? 'all') . '-to-' . ($toDate ?? 'all') . '.csv"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function productsExport(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $categoryId = $request->get('category_id');

        $query = \App\Models\Product::with('category');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->get();

        // Generate CSV content
        $csvContent = $this->generateProductsCsvContent($products);
        
        // Return CSV file with proper headers
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="products-report-' . ($fromDate ?? 'all') . '-to-' . ($toDate ?? 'all') . '.csv"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function generateCsvContent($sales)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // CSV headers
        fputcsv($output, [
            'Sale ID',
            'Date',
            'Time', 
            'Total Amount',
            'Payment Method',
            'Items Count',
            'Items Details'
        ]);
        
        foreach ($sales as $sale) {
            $items = $sale->items->map(function($item) {
                return $item->product->name . ' (x' . $item->quantity . ') - ₱' . number_format($item->total, 2);
            })->join('; ');
            
            fputcsv($output, [
                $sale->id,
                $sale->created_at->format('Y-m-d'),
                $sale->created_at->format('H:i:s'),
                '₱' . number_format($sale->total, 2),
                'GCash',
                $sale->items->count(),
                $items
            ]);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    private function generateInventoryCsvContent($movements)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // CSV headers
        fputcsv($output, [
            'Date',
            'Time',
            'Product Name',
            'SKU',
            'Movement Type',
            'Quantity',
            'Reason',
            'Related Sale ID'
        ]);
        
        foreach ($movements as $movement) {
            fputcsv($output, [
                $movement->created_at->format('Y-m-d'),
                $movement->created_at->format('H:i:s'),
                $movement->product ? $movement->product->name : 'Unknown Product',
                $movement->product ? $movement->product->sku : 'N/A',
                $movement->type === 'in' ? 'Stock In' : 'Stock Out',
                $movement->quantity,
                $movement->reason ?: 'N/A',
                $movement->sale_id ?: 'N/A'
            ]);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    private function generateProductsCsvContent($products)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // CSV headers
        fputcsv($output, [
            'SKU',
            'Product Name',
            'Category',
            'Price',
            'Stock',
            'Description',
            'Created Date'
        ]);
        
        foreach ($products as $product) {
            fputcsv($output, [
                $product->sku,
                $product->name,
                $product->category ? $product->category->name : 'Uncategorized',
                '₱' . number_format($product->price, 2),
                $product->stock ?? 0,
                $product->description ?: 'N/A',
                $product->created_at->format('Y-m-d')
            ]);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
}


