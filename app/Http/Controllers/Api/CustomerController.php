<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $q = $request->search;
            $query->where(fn ($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }

        return response()->json($query->orderByDesc('total_orders')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        return response()->json(Customer::create($data), 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'string|max:100',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
        $customer->update($data);

        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(null, 204);
    }

    public function orders(Customer $customer): JsonResponse
    {
        return response()->json($customer->orders()->with('items')->latest()->get());
    }
}
