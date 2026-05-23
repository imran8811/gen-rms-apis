<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('active')) {
            $query->where('is_active', true);
        }

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:150',
            'slug'           => 'nullable|string|max:100|unique:menu_items,slug',
            'description'    => 'nullable|string',
            'price_type'     => 'in:single,sized',
            'price'          => 'nullable|integer|min:0',
            'prices'         => 'nullable|array',
            'pizza_selection'=> 'nullable|array',
            'default_size'   => 'nullable|string|max:50',
            'tag'            => 'nullable|string|max:50',
            'is_special'     => 'boolean',
            'is_signature'   => 'boolean',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ]);

        return response()->json(MenuItem::create($data), 201);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        return response()->json($menuItem->load('category'));
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $data = $request->validate([
            'category_id'    => 'exists:categories,id',
            'name'           => 'string|max:150',
            'slug'           => 'nullable|string|max:100|unique:menu_items,slug,' . $menuItem->id,
            'description'    => 'nullable|string',
            'price_type'     => 'in:single,sized',
            'price'          => 'nullable|integer|min:0',
            'prices'         => 'nullable|array',
            'pizza_selection'=> 'nullable|array',
            'default_size'   => 'nullable|string|max:50',
            'tag'            => 'nullable|string|max:50',
            'is_special'     => 'boolean',
            'is_signature'   => 'boolean',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ]);
        $menuItem->update($data);

        return response()->json($menuItem);
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();
        return response()->json(null, 204);
    }
}
