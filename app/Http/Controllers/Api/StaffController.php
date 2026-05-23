<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Staff::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('active')) {
            $query->where('is_active', true);
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'role'      => 'required|in:Manager,Chef,Cashier,Rider,Waiter,Helper',
            'phone'     => 'nullable|string|max:20',
            'shift'     => 'nullable|string|max:50',
            'salary'    => 'integer|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notes'     => 'nullable|string',
        ]);

        return response()->json(Staff::create($data), 201);
    }

    public function show(Staff $staff): JsonResponse
    {
        return response()->json($staff);
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'string|max:100',
            'role'      => 'in:Manager,Chef,Cashier,Rider,Waiter,Helper',
            'phone'     => 'nullable|string|max:20',
            'shift'     => 'nullable|string|max:50',
            'salary'    => 'integer|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notes'     => 'nullable|string',
        ]);
        $staff->update($data);

        return response()->json($staff);
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $staff->delete();
        return response()->json(null, 204);
    }
}
