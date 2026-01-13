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

        // Prepare user data with full profile photo URL
        $userData = $user->toArray();
        $userData['profile_photo_url'] = $user->profile_photo_url;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $userData,
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

        // Prepare serviceman data with full image URLs
        $servicemanData = $serviceman->toArray();
        $servicemanData['profile_photo_url'] = $serviceman->profile_photo_url;
        $servicemanData['id_proof_image_url'] = $serviceman->id_proof_image_url;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'serviceman' => $servicemanData,
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

        // Prepare brahman data with full image URLs
        $brahmanData = $brahman->toArray();
        $brahmanData['profile_photo_url'] = $brahman->profile_photo_url;
        $brahmanData['id_proof_image_url'] = $brahman->id_proof_image_url;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'brahman' => $brahmanData,
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

    // Delete User Account
    public function deleteUserAccount(Request $request)
    {
        $user = $request->user();
        
        // Delete user's tokens
        $user->tokens()->delete();
        
        // Delete user account
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User account deleted successfully',
        ]);
    }

    // Delete Serviceman Account
    public function deleteServicemanAccount(Request $request)
    {
        $serviceman = $request->user();
        
        // Delete related records
        $serviceman->servicemanServicePrices()->delete();
        $serviceman->experiences()->delete();
        $serviceman->achievements()->delete();
        $serviceman->services()->detach();
        
        // Delete serviceman's tokens
        $serviceman->tokens()->delete();
        
        // Delete serviceman account
        $serviceman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Serviceman account deleted successfully',
        ]);
    }

    // Delete Brahman Account
    public function deleteBrahmanAccount(Request $request)
    {
        $brahman = $request->user();
        
        // Delete related records
        $brahman->experiences()->delete();
        $brahman->achievements()->delete();
        
        // Delete brahman's tokens
        $brahman->tokens()->delete();
        
        // Delete brahman account
        $brahman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brahman account deleted successfully',
        ]);
    }
}
