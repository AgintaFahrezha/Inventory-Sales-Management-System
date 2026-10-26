<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display dashboard summary and analytics
     */
    public function index(Request $request)
    {
        if (auth()->user()->isCustomer()) {
            return redirect()->route('customer.dashboard');
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Aggregate Metrics
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        $totalSalesCount = Sale::where('status', 'completed')->count();
        $totalPurchasesCount = Purchase::where('status', 'completed')->count();
        $totalTransactionsCount = $totalSalesCount + $totalPurchasesCount;

        $salesTodayRevenue = Sale::where('status', 'completed')
            ->whereDate('sold_at', $today)
            ->sum('total');

        $salesMonthRevenue = Sale::where('status', 'completed')
            ->where('sold_at', '>=', $startOfMonth)
            ->sum('total');

        $purchasesMonthExpenditure = Purchase::where('status', 'completed')
            ->where('purchased_at', '>=', $startOfMonth)
            ->sum('total');

        // Low stock products query (stock <= minimum_stock)
        $lowStockQuery = Product::with('category')
            ->whereRaw('stock <= minimum_stock');

        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockProducts = (clone $lowStockQuery)->orderBy('stock', 'asc')->take(5)->get();

        // Recent completed transactions
        $recentSales = Sale::with(['user', 'items.product'])
            ->latest('sold_at')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'user', 'items.product'])
            ->latest('purchased_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalTransactionsCount',
            'salesTodayRevenue',
            'salesMonthRevenue',
            'purchasesMonthExpenditure',
            'lowStockCount',
            'lowStockProducts',
            'recentSales',
            'recentPurchases'
        ));
    }
}
