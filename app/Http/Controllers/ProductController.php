<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->category_id, function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => $request->hasFile('image') ? 'image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
        ]);

        if (!auth()->check()) {
            $shop = \App\Models\Shop::first();
            if (!$shop) {
                $shop = \App\Models\Shop::create([
                    'name' => 'Toko Nuansa',
                    'address' => 'Alamat Default',
                ]);
            }
            $validated['shop_id'] = $shop->id;
        }

        // if ($request->hasFile('image')) {
        //     $uploadedFileUrl = cloudinary()->upload($request->file('image')->getRealPath())->getSecurePath();
        //     $validated['image'] = $uploadedFileUrl;
        // } else {
        //     // Karena di migrasi 'image' tidak nullable, beri default placeholder
        //     $validated['image'] = "https://ui-avatars.com/api/?name=" . urlencode($validated['name']) . "&background=random";
        // }
        if ($request->hasFile('image')) {
        /* Cloudinary (Commented)
        $uploadedFileUrl = cloudinary()->upload($request->file('image')->getRealPath())->getSecurePath();
        $validated['image'] = $uploadedFileUrl;
        */
        
        // Simpan Lokal
        $path = $request->file('image')->store('products', 'public');
        $validated['image'] = $path;
    } else {
        $validated['image'] = "https://ui-avatars.com/api/?name=" . urlencode($validated['name']) . "&background=random";
    }

        // Generate SKU jika tidak diisi
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(substr(md5(time() . $validated['name']), 0, 8));
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'unit' => 'sometimes|string|max:50',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'image' => $request->hasFile('image') ? 'image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
        ]);

        if ($request->hasFile('image')) {
            // Simpan Lokal
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
    }

    public function destroy(Product $product)
    {
        // Catatan: Menghapus dari Cloudinary membutuhkan public_id. 
        // Jika kita menyimpan full URL, kita perlu mengekstrak public_id-nya.
        // Untuk saat ini kita biarkan di Cloudinary atau hapus manual agar tidak kompleks.
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}
