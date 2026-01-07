<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brahman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrahmanController extends Controller
{
    public function index()
    {
        $brahmans = Brahman::where('status', 'active')
            ->where('availability_status', 'available')
            ->get()
            ->map(function ($brahman) {
                return [
                    'id' => $brahman->id,
                    'name' => $brahman->name,
                    'email' => $brahman->email,
                    'mobile_number' => $brahman->mobile_number,
                    'specialization' => $brahman->specialization,
                    'languages' => $brahman->languages,
                    'experience' => $brahman->experience,
                    'charges' => $brahman->charges,
                    'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
                    'availability_status' => $brahman->availability_status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brahmans,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $brahman = $request->user();

        $request->validate([
            'government_id' => 'nullable|string',
            'id_proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['government_id', 'address']);

        if ($request->hasFile('id_proof_image')) {
            if ($brahman->id_proof_image) {
                Storage::disk('public')->delete($brahman->id_proof_image);
            }
            $data['id_proof_image'] = $request->file('id_proof_image')->store('brahmans/id-proofs', 'public');
        }

        if ($request->hasFile('profile_photo')) {
            if ($brahman->profile_photo) {
                Storage::disk('public')->delete($brahman->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('brahmans/profiles', 'public');
        }

        $brahman->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $brahman,
        ]);
    }

    public function addExperience(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years' => 'nullable|integer|min:0',
            'organization' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $brahman = $request->user();
        $experience = \App\Models\BrahmanExperience::create([
            'brahman_id' => $brahman->id,
            'title' => $request->title,
            'description' => $request->description,
            'years' => $request->years,
            'organization' => $request->organization,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_current' => $request->is_current ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Experience added successfully',
            'data' => $experience,
        ], 201);
    }

    public function addAchievement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'achieved_date' => 'nullable|date',
        ]);

        $brahman = $request->user();
        $achievement = \App\Models\BrahmanAchievement::create([
            'brahman_id' => $brahman->id,
            'title' => $request->title,
            'description' => $request->description,
            'achieved_date' => $request->achieved_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Achievement added successfully',
            'data' => $achievement,
        ], 201);
    }
}
