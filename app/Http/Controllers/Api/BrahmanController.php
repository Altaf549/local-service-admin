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

    // Get Brahman Profile Data
    public function getProfileData(Request $request)
    {
        $brahman = $request->user();

        $profileData = [
            'id' => $brahman->id,
            'name' => $brahman->name,
            'email' => $brahman->email,
            'mobile_number' => $brahman->mobile_number,
            'government_id' => $brahman->government_id,
            'address' => $brahman->address,
            'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
            'id_proof_image' => $brahman->id_proof_image ? asset('storage/' . $brahman->id_proof_image) : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $profileData,
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
        // Include profile photo URL in response
        $brahmanData = $brahman->toArray();
        if ($brahman->profile_photo) {
            $brahmanData['profile_photo_url'] = asset('storage/' . $brahman->profile_photo);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $brahmanData,
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

    public function getExperiences(Request $request)
    {
        $brahman = $request->user();
        $experiences = \App\Models\BrahmanExperience::where('brahman_id', $brahman->id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'title' => $exp->title,
                    'description' => $exp->description,
                    'years' => $exp->years,
                    'organization' => $exp->organization,
                    'start_date' => $exp->start_date ? $exp->start_date->format('Y-m-d') : null,
                    'end_date' => $exp->end_date ? $exp->end_date->format('Y-m-d') : null,
                    'is_current' => $exp->is_current,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $experiences,
        ]);
    }

    public function getAchievements(Request $request)
    {
        $brahman = $request->user();
        $achievements = \App\Models\BrahmanAchievement::where('brahman_id', $brahman->id)
            ->orderBy('achieved_date', 'desc')
            ->get()
            ->map(function ($ach) {
                return [
                    'id' => $ach->id,
                    'title' => $ach->title,
                    'description' => $ach->description,
                    'achieved_date' => $ach->achieved_date ? $ach->achieved_date->format('Y-m-d') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    public function updateExperience(Request $request, $id)
    {
        $brahman = $request->user();
        $experience = \App\Models\BrahmanExperience::where('id', $id)
            ->where('brahman_id', $brahman->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years' => 'nullable|integer|min:0',
            'organization' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $experience->update($request->only(['title', 'description', 'years', 'organization', 'start_date', 'end_date', 'is_current']));

        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully',
            'data' => $experience,
        ]);
    }

    public function updateAchievement(Request $request, $id)
    {
        $brahman = $request->user();
        $achievement = \App\Models\BrahmanAchievement::where('id', $id)
            ->where('brahman_id', $brahman->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'achieved_date' => 'nullable|date',
        ]);

        $achievement->update($request->only(['title', 'description', 'achieved_date']));

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully',
            'data' => $achievement,
        ]);
    }

    public function deleteExperience(Request $request, $id)
    {
        $brahman = $request->user();
        $experience = \App\Models\BrahmanExperience::where('id', $id)
            ->where('brahman_id', $brahman->id)
            ->firstOrFail();

        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully',
        ]);
    }

    public function deleteAchievement(Request $request, $id)
    {
        $brahman = $request->user();
        $achievement = \App\Models\BrahmanAchievement::where('id', $id)
            ->where('brahman_id', $brahman->id)
            ->firstOrFail();

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Achievement deleted successfully',
        ]);
    }

    public function getDetails($id)
    {
        $brahman = Brahman::with(['experiences'])
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        // Get achievements directly to ensure they load
        $achievements = \App\Models\BrahmanAchievement::where('brahman_id', $brahman->id)
            ->orderBy('achieved_date', 'desc')
            ->get();

        $pujaPrices = \App\Models\BrahmanPujaPrice::with('puja.pujaType')
            ->where('brahman_id', $brahman->id)
            ->get()
            ->map(function ($price) {
                return [
                    'id' => $price->id,
                    'puja_id' => $price->puja_id,
                    'puja_name' => $price->puja->puja_name,
                    'image' => $price->puja->image ? asset('storage/' . $price->puja->image) : null,
                    'puja_type' => $price->puja->pujaType ? $price->puja->pujaType->type_name : null,
                    'duration' => $price->puja->duration,
                    'price' => $price->price,
                    'description' => $price->puja->description,
                    'material_file' => $price->material_file ? asset('storage/' . $price->material_file) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $brahman->id,
                'name' => $brahman->name,
                'email' => $brahman->email,
                'mobile_number' => $brahman->mobile_number,
                'specialization' => $brahman->specialization,
                'languages' => $brahman->languages,
                'experience' => $brahman->experience,
                'charges' => $brahman->charges,
                'availability_status' => $brahman->availability_status,
                'government_id' => $brahman->government_id,
                'address' => $brahman->address,
                'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
                'id_proof_image' => $brahman->id_proof_image ? asset('storage/' . $brahman->id_proof_image) : null,
                'experiences' => $brahman->experiences ? $brahman->experiences->map(function ($exp) {
                    return [
                        'id' => $exp->id,
                        'title' => $exp->title,
                        'description' => $exp->description,
                        'years' => $exp->years,
                        'organization' => $exp->organization,
                        'start_date' => $exp->start_date ? $exp->start_date->format('Y-m-d') : null,
                        'end_date' => $exp->end_date ? $exp->end_date->format('Y-m-d') : null,
                        'is_current' => $exp->is_current,
                    ];
                }) : [],
                'achievements' => $achievements->map(function ($ach) {
                    return [
                        'id' => $ach->id,
                        'title' => $ach->title,
                        'description' => $ach->description,
                        'achieved_date' => $ach->achieved_date ? $ach->achieved_date->format('Y-m-d') : null,
                    ];
                }),
                'services' => $pujaPrices,
            ],
        ]);
    }

    // Get Brahman Status
    public function getStatus($id)
    {
        $brahman = Brahman::where('id', $id)
            ->first();

        if (!$brahman) {
            return response()->json([
                'success' => false,
                'message' => 'Brahman not found',
                'errors' => [
                    'id' => ['Brahman not found']
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $brahman->id,
                'name' => $brahman->name,
                'email' => $brahman->email,
                'mobile_number' => $brahman->mobile_number,
                'specialization' => $brahman->specialization,
                'languages' => $brahman->languages,
                'experience' => $brahman->experience,
                'charges' => $brahman->charges,
                'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
                'profile_photo_url' => $brahman->profile_photo ? url('storage/' . $brahman->profile_photo) : null,
                'id_proof_image' => $brahman->id_proof_image ? asset('storage/' . $brahman->id_proof_image) : null,
                'id_proof_image_url' => $brahman->id_proof_image ? url('storage/' . $brahman->id_proof_image) : null,
                'government_id' => $brahman->government_id,
                'address' => $brahman->address,
                'status' => $brahman->status,
                'availability_status' => $brahman->availability_status,
                'is_active' => $brahman->status === 'active',
                'is_available' => $brahman->availability_status === 'available',
            ]
        ]);
    }
}
