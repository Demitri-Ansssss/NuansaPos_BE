<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * List all staff in the current shop (auto-scoped by BelongsToShop on User model).
     */
    public function index(Request $request)
    {
        $users = User::where('shop_id', $request->user()->shop_id)
            ->latest()
            ->get()
            ->makeHidden(['password', 'remember_token']);

        return response()->json($users);
    }

    /**
     * Owner creates a new staff member (admin or cashier) for their shop.
     */
    public function store(Request $request)
    {
        // Only owners can add staff
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat menambah staf.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,cashier',
        ]);

        $user = User::create([
            'shop_id' => $request->user()->shop_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json($user->makeHidden(['password', 'remember_token']), 201);
    }

    /**
     * Update a staff member's info.
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat mengubah data staf.'], 403);
        }

        $staff = User::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $staff->id,
            'role' => 'sometimes|required|in:owner,admin,cashier',
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $staff->update($validated);

        return response()->json($staff->makeHidden(['password', 'remember_token']));
    }

    /**
     * Delete a staff member. Owner cannot delete themselves.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat menghapus staf.'], 403);
        }

        $staff = User::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        if ($staff->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 422);
        }

        $staff->delete();

        return response()->json(['message' => 'Staf berhasil dihapus.']);
    }
}
