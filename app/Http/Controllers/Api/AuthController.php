<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Serviceman;
use App\Models\Brahman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // User Registration
    public function userRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|string|unique:users,mobile_number',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    // User Login
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'user')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    // Serviceman Registration
    public function servicemanRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:servicemen,email',
            'mobile_number' => 'required|string|unique:servicemen,mobile_number',
            'password' => 'required|string|min:6',
        ]);

        $serviceman = Serviceman::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'phone' => $request->mobile_number,
            'status' => 'inactive',
            'availability_status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Serviceman registered successfully',
            'data' => $serviceman,
        ], 201);
    }

    // Serviceman Login
    public function servicemanLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $serviceman = Serviceman::where('email', $request->email)->first();

        if (!$serviceman || !Hash::check($request->password, $serviceman->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Allow login even if status is inactive - only admin can change status

        $token = $serviceman->createToken('serviceman_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'serviceman' => $serviceman,
                'token' => $token,
            ],
        ]);
    }

    // Brahman Registration
    public function brahmanRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:brahmans,email',
            'mobile_number' => 'required|string|unique:brahmans,mobile_number',
            'password' => 'required|string|min:6',
        ]);

        $brahman = Brahman::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'status' => 'inactive', // new brahmans start as inactive
            'availability_status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brahman registered successfully',
            'data' => $brahman,
        ], 201);
    }

    // Brahman Login
    public function brahmanLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $brahman = Brahman::where('email', $request->email)->first();

        if (!$brahman || !Hash::check($request->password, $brahman->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Allow login even if status is inactive - only admin can change status

        $token = $brahman->createToken('brahman_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'brahman' => $brahman,
                'token' => $token,
            ],
        ]);
    }

    // Logout (for all user types)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
