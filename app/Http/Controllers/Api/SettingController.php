<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $allowed = [
            'restaurant_name', 'tagline', 'address', 'phone', 'whatsapp',
            'timing', 'currency', 'tax_rate', 'default_delivery_charge',
            'receipt_footer', 'receipt_copies', 'table_count',
        ];

        foreach ($request->only($allowed) as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(Setting::all()->pluck('value', 'key'));
    }
}
