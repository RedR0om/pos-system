<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    /**
     * Create admin user if none exists
     */
    public function createAdmin(Request $request)
    {
        // Check if any users exist
        if (User::count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Users already exist. Admin user cannot be created via this route.'
            ], 403);
        }

        // Get credentials from request or use defaults
        $name = $request->input('name', 'Admin');
        $email = $request->input('email', 'admin@example.com');
        $password = $request->input('password', 'admin123');

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully!',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password, // Only shown once for security
                    'role' => $user->role,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin user: ' . $e->getMessage()
            ], 500);
        }
    }
}
