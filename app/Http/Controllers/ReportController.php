<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
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
        $limit = $request->get('limit', 5);

        $products = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('product:id,name,price')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }
}
