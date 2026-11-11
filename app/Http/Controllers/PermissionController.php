<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
{
    private function getPermissionsFilePath()
    {
        return 'permissions.json';
    }

    private function getDefaultPermissions()
    {
        return [
            [
                "category" => "Inventori & Barang",
                "items" => [
                    [
                        "key" => "view_products",
                        "label" => "Lihat Produk",
                        "description" => "Melihat daftar produk & kategori",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => true]
                    ],
                    [
                        "key" => "manage_products",
                        "label" => "Kelola Produk",
                        "description" => "Tambah, Edit, Hapus produk",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => false]
                    ],
                    [
                        "key" => "update_stock",
                        "label" => "Update Stok Cepat",
                        "description" => "Mengubah jumlah stok langsung",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => true]
                    ]
                ]
            ],
            [
                "category" => "Penjualan & Kasir",
                "items" => [
                    [
                        "key" => "create_transaction",
                        "label" => "Buat Transaksi",
                        "description" => "Melakukan input penjualan di kasir",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => true]
                    ],
                    [
                        "key" => "apply_discount",
                        "label" => "Berikan Diskon",
                        "description" => "Mengubah diskon manual saat checkout",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => false]
                    ],
                    [
                        "key" => "void_transaction",
                        "label" => "Void Transaksi",
                        "description" => "Membatalkan pesanan yang sudah tercetak",
                        "roles" => ["owner" => true, "admin" => false, "cashier" => false]
                    ]
                ]
            ],
            [
                "category" => "Laporan & Keamanan",
                "items" => [
                    [
                        "key" => "view_reports",
                        "label" => "Lihat Laporan",
                        "description" => "Melihat data omset & produk terlaris",
                        "roles" => ["owner" => true, "admin" => true, "cashier" => false]
                    ],
                    [
                        "key" => "manage_users",
                        "label" => "Kelola Karyawan",
                        "description" => "Menambah & menghapus staf",
                        "roles" => ["owner" => true, "admin" => false, "cashier" => false]
                    ],
                    [
                        "key" => "manage_settings",
                        "label" => "Pengaturan Sistem",
                        "description" => "Mengubah PPN & alamat struk",
                        "roles" => ["owner" => true, "admin" => false, "cashier" => false]
                    ]
                ]
            ]
        ];
    }

    public function index(Request $request)
    {
        $shopId = $request->user()->shop_id;
        $dbPermissions = \App\Models\RolePermission::where('shop_id', $shopId)->get();

        $defaultPermissions = $this->getDefaultPermissions();

        // Map database overrides back onto the response structure
        $mappedPermissions = array_map(function ($category) use ($dbPermissions) {
            $category['items'] = array_map(function ($item) use ($dbPermissions) {
                foreach ($item['roles'] as $roleName => $defaultVal) {
                    $record = $dbPermissions->first(function ($p) use ($roleName, $item) {
                        return $p->role === $roleName && $p->permission_key === $item['key'];
                    });

                    if ($record) {
                        $item['roles'][$roleName] = (bool) $record->is_allowed;
                    }
                }
                return $item;
            }, $category['items']);
            return $category;
        }, $defaultPermissions);

        return response()->json($mappedPermissions);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array'
        ]);

        $shopId = $request->user()->shop_id;

        foreach ($validated['permissions'] as $category) {
            if (!isset($category['items']) || !is_array($category['items'])) {
                continue;
            }

            foreach ($category['items'] as $item) {
                if (!isset($item['key']) || !isset($item['roles']) || !is_array($item['roles'])) {
                    continue;
                }

                foreach ($item['roles'] as $role => $isAllowed) {
                    \App\Models\RolePermission::updateOrCreate(
                        [
                            'shop_id' => $shopId,
                            'role' => $role,
                            'permission_key' => $item['key'],
                        ],
                        [
                            'is_allowed' => (bool) $isAllowed,
                        ]
                    );
                }
            }
        }

        return response()->json([
            'message' => 'Permissions updated successfully',
            'permissions' => $validated['permissions']
        ]);
    }
}
