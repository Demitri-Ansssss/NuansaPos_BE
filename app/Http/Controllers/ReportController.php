<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function dashboardStats(Request $request)
    {
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $recentOrders = Order::latest()->limit(5)->get();

        return response()->json([
            'totalRevenue' => (float) $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function salesSummary(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $dailySales = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_price');

        $monthlySales = Order::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('total_price');

        $totalTransactions = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'today_sales' => $dailySales,
            'monthly_sales' => $monthlySales,
            'today_transactions' => $totalTransactions,
        ]);
    }

    public function bestSellingProducts(Request $request)
    {
        $limit = (int) $request->get('limit', 5);

        $products = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $request->user()->shop_id)
            ->select(
                'products.id as product_id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }
}
