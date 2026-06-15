<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $query = Order::where('status', 'completed');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $period = $request->get('period', 'today');
            match ($period) {
                'today' => $query->whereDate('created_at', today()),
                'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                'year'  => $query->whereYear('created_at', now()->year),
                default => $query->whereDate('created_at', today()),
            };
        }

        $orders   = $query->get();
        $revenue  = $orders->sum('total');
        $count    = $orders->count();
        $avgValue = $count > 0 ? (int) round($revenue / $count) : 0;

        $byType = $orders->groupBy('order_type')->map(fn ($g) => [
            'count'   => $g->count(),
            'revenue' => $g->sum('total'),
        ]);

        $foodpandaOrders = $orders->where('source', 'foodpanda');

        // Orders placed within fixed Pakistan-time windows (hours are 24h).
        // Timestamps are stored in UTC, so convert to the restaurant's local zone first.
        $afternoonOrders = $orders->filter(
            fn ($o) => in_array((int) $o->created_at->copy()->setTimezone('Asia/Karachi')->format('H'), [14, 15], true)
        );
        $eveningOrders = $orders->filter(
            fn ($o) => in_array((int) $o->created_at->copy()->setTimezone('Asia/Karachi')->format('H'), [16, 17], true)
        );

        return response()->json([
            'revenue'    => $revenue,
            'orders'     => $count,
            'avg_value'  => $avgValue,
            'deliveries' => $orders->where('order_type', 'Delivery')->count(),
            'by_type'    => $byType,
            'foodpanda'  => [
                'orders'  => $foodpandaOrders->count(),
                'revenue' => $foodpandaOrders->sum('total'),
            ],
            'window_2_4' => [
                'orders'  => $afternoonOrders->count(),
                'revenue' => $afternoonOrders->sum('total'),
            ],
            'window_4_6' => [
                'orders'  => $eveningOrders->count(),
                'revenue' => $eveningOrders->sum('total'),
            ],
        ]);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');

        $dateFilter = match ($period) {
            'today' => ['>=', today()],
            'week'  => ['>=', now()->startOfWeek()],
            'year'  => ['>=', now()->startOfYear()],
            default => ['>=', now()->startOfMonth()],
        };

        $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->where('orders.status', 'completed')
            ->where('orders.created_at', $dateFilter[0], $dateFilter[1])
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name as category', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.line_total) as revenue'))
            ->orderByDesc('revenue')
            ->get();

        return response()->json($data);
    }

    public function byDay(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);

        $data = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as revenue'))
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }
}
