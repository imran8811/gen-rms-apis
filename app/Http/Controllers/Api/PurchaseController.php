<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Purchase::query();

        if ($request->has('date')) {
            $query->where('date', $request->date);
        } elseif ($request->has('month')) {
            [$year, $mon] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $mon);
        }

        return response()->json(
            $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date'        => 'required|date',
            'item_name'   => 'required|string|max:200',
            'vendor_name' => 'required|string|max:200',
            'rate'        => 'required|numeric|min:0',
            'quantity'    => 'required|numeric|min:0.001',
            'unit'        => 'required|in:gram,kg,bottle,pcs,shashay',
        ]);

        $data['total_amount'] = round($data['rate'] * $data['quantity'], 2);

        return response()->json(Purchase::create($data), 201);
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $purchase->delete();
        return response()->json(null, 204);
    }
}
