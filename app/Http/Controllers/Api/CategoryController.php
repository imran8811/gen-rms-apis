<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with(['menuItems' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories->map(fn($cat) => [
            'id'         => $cat->slug,
            'name'       => $cat->name,
            'type'       => $cat->type,
            'sizes'      => $cat->sizes,
            'comingSoon' => $cat->is_coming_soon,
            'items'      => $cat->menuItems->map(fn($item) => array_filter([
                'id'             => $item->slug,
                'name'           => $item->name,
                'description'    => $item->description,
                'price'          => $item->price,
                'prices'         => $item->prices,
                'tag'            => $item->tag,
                'special'        => $item->is_special ?: null,
                'signature'      => $item->is_signature ?: null,
                'pizzaSelection' => $item->pizza_selection,
                'defaultSize'    => $item->default_size,
            ], fn($v) => $v !== null)),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'type'         => 'in:single,sized',
            'sizes'        => 'nullable|array',
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'is_coming_soon' => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('menuItems'));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'string|max:100',
            'type'         => 'in:single,sized',
            'sizes'        => 'nullable|array',
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'is_coming_soon' => 'boolean',
        ]);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
