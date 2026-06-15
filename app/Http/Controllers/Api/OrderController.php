<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items', 'customer'])->latest();

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('order_type')) {
            $query->where('order_type', $request->order_type);
        }
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        return response()->json($query->paginate(50));
    }

    /**
     * Sequential bill number: starts at 3005 and increments by 1 per order.
     */
    private function computeNextNumber(): int
    {
        $lastNumber = (int) Order::max(DB::raw('CAST(order_number AS UNSIGNED)'));
        return max($lastNumber + 1, 3005);
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json(['next' => $this->computeNextNumber()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_type'       => 'required|in:Dine-in,Takeaway,Delivery',
            'source'           => 'nullable|in:pos,foodpanda',
            'customer_id'      => 'nullable|exists:customers,id',
            'subtotal'         => 'required|integer|min:0',
            'delivery_charge'  => 'integer|min:0',
            'extra_topping'    => 'integer|min:0',
            'total'            => 'required|integer|min:0',
            'status'           => 'in:completed,cancelled',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.menu_item_id'  => 'nullable|exists:menu_items,id',
            'items.*.item_name'     => 'required|string',
            'items.*.size'          => 'nullable|string',
            'items.*.unit_price'    => 'required|integer|min:0',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.line_total'    => 'required|integer|min:0',
            'items.*.deal_selections' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($data) {
            $orderNumber = (string) $this->computeNextNumber();
            $data['source'] = $data['source'] ?? 'pos';
            $order = Order::create([...$data, 'order_number' => $orderNumber]);
            $order->items()->createMany($data['items']);

            // Update customer stats if linked
            if ($order->customer_id) {
                Customer::where('id', $order->customer_id)->increment('total_orders');
                Customer::where('id', $order->customer_id)->increment('total_spent', $order->total);
                Customer::where('id', $order->customer_id)->update(['last_order_at' => now()]);
            }

            return response()->json($order->load('items'), 201);
        });
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['items', 'customer']));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status'             => 'sometimes|in:completed,cancelled',
            'order_type'         => 'sometimes|in:Dine-in,Takeaway,Delivery',
            'notes'              => 'sometimes|nullable|string|max:500',
            'delivery_charge'    => 'sometimes|integer|min:0',
            'extra_topping'      => 'sometimes|integer|min:0',
            'items'              => 'sometimes|array|min:1',
            'items.*.id'         => 'required_with:items|integer',
            'items.*.quantity'   => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|integer|min:0',
        ]);

        return DB::transaction(function () use ($data, $order) {
            if (isset($data['items'])) {
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $lineTotal;
                    $order->items()->where('id', $item['id'])->update([
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $lineTotal,
                    ]);
                }
                unset($data['items']);
                $data['subtotal'] = $subtotal;
                $data['total']    = $subtotal
                    + ($data['delivery_charge'] ?? $order->delivery_charge)
                    + ($data['extra_topping']   ?? $order->extra_topping);
            }

            $order->update($data);
            return response()->json($order->refresh()->load('items'));
        });
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(null, 204);
    }
}
