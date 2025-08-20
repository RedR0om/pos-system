<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->only(['id', 'name', 'email', 'role', 'status', 'created_at'])
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->only(['id', 'name', 'email', 'role', 'status', 'created_at'])
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->only(['id', 'name', 'email', 'role', 'status', 'created_at'])
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => $user->only(['id', 'name', 'email', 'role', 'status', 'created_at'])
        ]);
    }
}
