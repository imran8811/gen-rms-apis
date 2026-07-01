<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function dateFilter(string $period)
    {
        return match ($period) {
            'today' => [today(), today()->endOfDay()],
            'week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'year'  => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function summary(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->dateFilter($period);

        $orders  = Order::where('status', 'completed')->whereBetween('created_at', [$from, $to]);
        $revenue = $orders->sum('total');
        $count   = $orders->count();

        return response()->json([
            'period'    => $period,
            'from'      => $from->toDateString(),
            'to'        => $to->toDateString(),
            'revenue'   => $revenue,
            'orders'    => $count,
            'avg_order' => $count > 0 ? (int) round($revenue / $count) : 0,
            'growth'    => 0,
        ]);
    }

    public function topItems(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $limit  = (int) $request->get('limit', 10);
        // 'qty' = most units sold, 'revenue' = top earners (default, backward compatible).
        $sort   = $request->get('sort') === 'qty' ? 'qty' : 'revenue';
        [$from, $to] = $this->dateFilter($period);

        $items = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->leftJoin('categories', 'menu_items.category_id', '=', 'categories.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_items.item_name')
            ->select(
                'order_items.item_name as name',
                DB::raw('MAX(categories.name) as category'),
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->orderByDesc($sort)
            ->limit($limit)
            ->get();

        return response()->json($items->values()->map(function ($item, $i) {
            $item->rank = $i + 1;
            return $item;
        }));
    }

    public function byCategory(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->dateFilter($period);

        $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('categories.id', 'categories.name')
            ->select(
                'categories.name as category',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.line_total) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders')
            )
            ->orderByDesc('revenue')
            ->get();

        $total = $data->sum('revenue');
        $data->each(fn ($row) => $row->share = $total > 0 ? round(($row->revenue / $total) * 100) : 0);

        return response()->json($data);
    }
}
