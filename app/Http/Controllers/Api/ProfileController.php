<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Serviceman;
use App\Models\Brahman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'mobile_number' => 'sometimes|string|unique:users,mobile_number,' . $user->id,
            'address' => 'sometimes|string|max:1000',
            'new_password' => 'sometimes|string|min:6',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $data = $request->only(['name', 'email', 'mobile_number', 'address']);

        // Update password if new password is provided
        if ($request->has('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            // Store new profile photo
            $data['profile_photo'] = $request->file('profile_photo')->store('users/profiles', 'public');
        }

        $user->update($data);

        // Include profile photo URL in response
        $userData = $user->toArray();
        if ($user->profile_photo) {
            $userData['profile_photo_url'] = asset('storage/' . $user->profile_photo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $userData,
        ]);
    }

    // Serviceman Profile Update
    public function updateServicemanProfile(Request $request)
    {
        $serviceman = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:servicemen,email,' . $serviceman->id,
            'mobile_number' => 'sometimes|string|unique:servicemen,mobile_number,' . $serviceman->id,
            'address' => 'sometimes|string|max:1000',
            'new_password' => 'sometimes|string|min:6',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $serviceman->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['Current password is incorrect']
                ]
            ], 422);
        }

        $data = $request->only(['name', 'email', 'mobile_number', 'address']);

        // Update password if new password is provided
        if ($request->has('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($serviceman->profile_photo) {
                Storage::disk('public')->delete($serviceman->profile_photo);
            }
            // Store new profile photo
            $data['profile_photo'] = $request->file('profile_photo')->store('servicemen/profiles', 'public');
        }

        $serviceman->update($data);

        // Include profile photo URL in response
        $servicemanData = $serviceman->toArray();
        if ($serviceman->profile_photo) {
            $servicemanData['profile_photo_url'] = asset('storage/' . $serviceman->profile_photo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Serviceman profile updated successfully',
            'data' => $servicemanData,
        ]);
    }

    // Brahman Profile Update
    public function updateBrahmanProfile(Request $request)
    {
        $brahman = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:brahmans,email,' . $brahman->id,
            'mobile_number' => 'sometimes|string|unique:brahmans,mobile_number,' . $brahman->id,
            'address' => 'sometimes|string|max:1000',
            'new_password' => 'sometimes|string|min:6',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $brahman->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['Current password is incorrect']
                ]
            ], 422);
        }

        $data = $request->only(['name', 'email', 'mobile_number', 'address']);

        // Update password if new password is provided
        if ($request->has('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($brahman->profile_photo) {
                Storage::disk('public')->delete($brahman->profile_photo);
            }
            // Store new profile photo
            $data['profile_photo'] = $request->file('profile_photo')->store('brahmans/profiles', 'public');
        }

        $brahman->update($data);

        // Include profile photo URL in response
        $brahmanData = $brahman->toArray();
        if ($brahman->profile_photo) {
            $brahmanData['profile_photo_url'] = asset('storage/' . $brahman->profile_photo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brahman profile updated successfully',
            'data' => $brahmanData,
        ]);
    }
}
