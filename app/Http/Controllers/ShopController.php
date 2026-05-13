<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::all();
        return response()->json($shops);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $shop = Shop::create([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'Shop created successfully',
            
            'shop' => $shop,
        ]);
    }

    public function show($id)
    {
        $shop = Shop::find($id);
        return response()->json($shop);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::find($id);
        $shop->update($request->all());
        return response()->json($shop);
    }

    public function destroy($id)
    {
        $shop = Shop::find($id);
        $shop->delete();
        return response()->json($shop);
    }
}
