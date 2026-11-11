<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(Request $request)
{
    $shopId = $request->user()?->shop_id;
    
    if (!$shopId) {
        return response()->json(['message' => 'Toko tidak ditemukan untuk user ini.'], 422);
    }
    
    $setting = Setting::firstOrCreate(
        ['shop_id' => $shopId],
        [
            'phone' => '',
            'tax_rate' => 10,
            'discount_threshold' => 5,
            'printer_ip' => '192.168.1.100',
            'auto_print' => true
        ]
    );

    return response()->json($setting);
}


    public function update(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:50',
            'tax_rate' => 'required|integer|min:0|max:100',
            'discount_threshold' => 'required|integer|min:0|max:100',
            'printer_ip' => 'required|string|max:100',
            'auto_print' => 'required|boolean',
        ]);

        $setting = Setting::updateOrCreate(
            ['shop_id' => $request->user()->shop_id],
            $validated
        );

        return response()->json($setting);
    }
}
