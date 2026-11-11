<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Enforce multi-tenant: only return the user's own shop
        $shop = Shop::where('id', $request->user()->shop_id)->get();
        return response()->json($shop);
    }

    public function create(Request $request)
    {
        // Only allow owner role to create shops (or restrict to superadmin if defined)
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat membuat toko.'], 403);
        }

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

    public function show(Request $request, $id)
    {
        if ((int)$request->user()->shop_id !== (int)$id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        $shop = Shop::findOrFail($id);
        return response()->json($shop);
    }

    public function update(Request $request, $id)
    {
        if ((int)$request->user()->shop_id !== (int)$id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat mengubah toko.'], 403);
        }

        $shop = Shop::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
        ]);

        $shop->update($request->only(['name', 'address']));
        return response()->json($shop);
    }

    public function destroy(Request $request, $id)
    {
        if ((int)$request->user()->shop_id !== (int)$id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat menghapus toko.'], 403);
        }

        $shop = Shop::findOrFail($id);
        $shop->delete();
        return response()->json(['message' => 'Toko berhasil dihapus.', 'shop' => $shop]);
    }
}
