<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serviceman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicemanController extends Controller
{
    public function index()
    {
        $servicemen = Serviceman::where('status', 'active')
            ->where('availability_status', 'available')
            ->with('category')
            ->get()
            ->map(function ($serviceman) {
                return [
                    'id' => $serviceman->id,
                    'name' => $serviceman->name,
                    'phone' => $serviceman->phone,
                    'email' => $serviceman->email,
                    'mobile_number' => $serviceman->mobile_number,
                    'category' => [
                        'id' => $serviceman->category->id ?? null,
                        'category_name' => $serviceman->category->category_name ?? null,
                    ],
                    'experience' => $serviceman->experience,
                    'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
                    'availability_status' => $serviceman->availability_status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $servicemen,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $serviceman = $request->user();

        $request->validate([
            'government_id' => 'nullable|string',
            'id_proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['government_id', 'address']);

        if ($request->hasFile('id_proof_image')) {
            if ($serviceman->id_proof_image) {
                Storage::disk('public')->delete($serviceman->id_proof_image);
            }
            $data['id_proof_image'] = $request->file('id_proof_image')->store('servicemen/id-proofs', 'public');
        }

        if ($request->hasFile('profile_photo')) {
            if ($serviceman->profile_photo) {
                Storage::disk('public')->delete($serviceman->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('servicemen/profiles', 'public');
        }

        $serviceman->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $serviceman,
        ]);
    }

    public function addExperience(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years' => 'nullable|integer|min:0',
            'company' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $serviceman = $request->user();
        $experience = \App\Models\ServicemanExperience::create([
            'serviceman_id' => $serviceman->id,
            'title' => $request->title,
            'description' => $request->description,
            'years' => $request->years,
            'company' => $request->company,
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

        $serviceman = $request->user();
        $achievement = \App\Models\ServicemanAchievement::create([
            'serviceman_id' => $serviceman->id,
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
