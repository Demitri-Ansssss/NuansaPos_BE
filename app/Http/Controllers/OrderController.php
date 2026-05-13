<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'user'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $totalPrice = 0;
                $orderItems = [];

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $totalPrice += $subtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                        'notes' => $item['notes'] ?? null,
                    ];

                    // Kurangi stok
                    $product->decrement('stock', $item['quantity']);
                }

                // Hitung final total (setelah pajak & diskon)
                $discount = $request->discount_amount ?? 0;
                $tax = $request->tax_amount ?? 0;
                $finalTotal = ($totalPrice - $discount) + $tax;
                $changeAmount = $request->paid_amount - $finalTotal;

                if ($changeAmount < 0) {
                    throw new \Exception("Jumlah bayar kurang.");
                }

                $order = Order::create([
                    'user_id' => $request->user()->id, 
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'customer_name' => $request->customer_name,
                    'total_price' => $finalTotal,
                    'status' => 'completed',
                    'payment_method' => $request->payment_method,
                    'paid_amount' => $request->paid_amount,
                    'change_amount' => $changeAmount,
                    'tax_amount' => $tax,
                    'discount_amount' => $discount,
                ]);

                $order->items()->createMany($orderItems);

                return response()->json([
                    'message' => 'Order berhasil dibuat',
                    'data' => $order->load('items.product')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function generateInvoiceNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', Carbon::today())->latest()->first();
        $sequence = $lastOrder ? (int) substr($lastOrder->invoice_number, -4) + 1 : 1;
        
        return 'INV-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
