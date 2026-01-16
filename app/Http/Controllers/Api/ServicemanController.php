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

    // Get Serviceman Profile Data
    public function getProfileData(Request $request)
    {
        $serviceman = $request->user();

        $profileData = [
            'id' => $serviceman->id,
            'name' => $serviceman->name,
            'email' => $serviceman->email,
            'mobile_number' => $serviceman->mobile_number,
            'government_id' => $serviceman->government_id,
            'address' => $serviceman->address,
            'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
            'id_proof_image' => $serviceman->id_proof_image ? asset('storage/' . $serviceman->id_proof_image) : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $profileData,
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
$servicemanData = $serviceman->toArray();
        if ($serviceman->profile_photo) {
            $servicemanData['profile_photo_url'] = asset('storage/' . $serviceman->profile_photo);
        }
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $servicemanData,
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

    public function getExperiences(Request $request)
    {
        $serviceman = $request->user();
        $experiences = \App\Models\ServicemanExperience::where('serviceman_id', $serviceman->id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'title' => $exp->title,
                    'description' => $exp->description,
                    'years' => $exp->years,
                    'company' => $exp->company,
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
        $serviceman = $request->user();
        $achievements = \App\Models\ServicemanAchievement::where('serviceman_id', $serviceman->id)
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
        $serviceman = $request->user();
        $experience = \App\Models\ServicemanExperience::where('id', $id)
            ->where('serviceman_id', $serviceman->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years' => 'nullable|integer|min:0',
            'company' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $experience->update($request->only(['title', 'description', 'years', 'company', 'start_date', 'end_date', 'is_current']));

        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully',
            'data' => $experience,
        ]);
    }

    public function updateAchievement(Request $request, $id)
    {
        $serviceman = $request->user();
        $achievement = \App\Models\ServicemanAchievement::where('id', $id)
            ->where('serviceman_id', $serviceman->id)
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
        $serviceman = $request->user();
        $experience = \App\Models\ServicemanExperience::where('id', $id)
            ->where('serviceman_id', $serviceman->id)
            ->firstOrFail();

        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully',
        ]);
    }

    public function deleteAchievement(Request $request, $id)
    {
        $serviceman = $request->user();
        $achievement = \App\Models\ServicemanAchievement::where('id', $id)
            ->where('serviceman_id', $serviceman->id)
            ->firstOrFail();

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Achievement deleted successfully',
        ]);
    }

    public function getDetails($id)
    {
        $serviceman = Serviceman::with(['experiences', 'category'])
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        // Get achievements directly to ensure they load
        $achievements = \App\Models\ServicemanAchievement::where('serviceman_id', $serviceman->id)
            ->orderBy('achieved_date', 'desc')
            ->get();

        // Get services from serviceman_service_prices table (custom prices)
        $servicesWithPrices = \App\Models\ServicemanServicePrice::with('service.category')
            ->where('serviceman_id', $serviceman->id)
            ->get()
            ->map(function ($price) {
                return [
                    'id' => $price->id,
                    'service_id' => $price->service_id,
                    'service_name' => $price->service->service_name,
                    'category' => $price->service->category ? [
                        'id' => $price->service->category->id,
                        'category_name' => $price->service->category->category_name,
                    ] : null,
                    'duration' => $price->service->description,
                    'price' => $price->price,
                    'description' => $price->service->description,
                    'image' => $price->service->image ? asset('storage/' . $price->service->image) : null,
                ];
            });

        // Get services from pivot table (default prices)
        $servicesFromPivot = $serviceman->services()
            ->with('category')
            ->where('status', 'active')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => null, // No price record ID
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'category' => $service->category ? [
                        'id' => $service->category->id,
                        'category_name' => $service->category->category_name,
                    ] : null,
                    'duration' => $service->description,
                    'price' => $service->price,
                    'description' => $service->description,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                ];
            });

        // Merge both collections and remove duplicates by service_id
        $allServices = $servicesWithPrices->concat($servicesFromPivot)->unique('service_id');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $serviceman->id,
                'name' => $serviceman->name,
                'email' => $serviceman->email,
                'phone' => $serviceman->phone,
                'mobile_number' => $serviceman->mobile_number,
                'category' => $serviceman->category ? [
                    'id' => $serviceman->category->id,
                    'category_name' => $serviceman->category->category_name,
                ] : null,
                'experience' => $serviceman->experience,
                'availability_status' => $serviceman->availability_status,
                'government_id' => $serviceman->government_id,
                'address' => $serviceman->address,
                'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
                'id_proof_image' => $serviceman->id_proof_image ? asset('storage/' . $serviceman->id_proof_image) : null,
                'experiences' => $serviceman->experiences ? $serviceman->experiences->map(function ($exp) {
                    return [
                        'id' => $exp->id,
                        'title' => $exp->title,
                        'description' => $exp->description,
                        'years' => $exp->years,
                        'company' => $exp->company,
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
                'services' => $allServices,
            ],
        ]);
    }

    // Get Serviceman Status
    public function getStatus($id)
    {
        $serviceman = Serviceman::with('category')
            ->where('id', $id)
            ->first();

        if (!$serviceman) {
            return response()->json([
                'success' => false,
                'message' => 'Serviceman not found',
                'errors' => [
                    'id' => ['Serviceman not found']
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $serviceman->id,
                'name' => $serviceman->name,
                'email' => $serviceman->email,
                'mobile_number' => $serviceman->mobile_number,
                'phone' => $serviceman->phone,
                'category' => $serviceman->category ? [
                    'id' => $serviceman->category->id,
                    'category_name' => $serviceman->category->category_name,
                ] : null,
                'experience' => $serviceman->experience,
                'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
                'profile_photo_url' => $serviceman->profile_photo ? url('storage/' . $serviceman->profile_photo) : null,
                'id_proof_image' => $serviceman->id_proof_image ? asset('storage/' . $serviceman->id_proof_image) : null,
                'id_proof_image_url' => $serviceman->id_proof_image ? url('storage/' . $serviceman->id_proof_image) : null,
                'government_id' => $serviceman->government_id,
                'address' => $serviceman->address,
                'status' => $serviceman->status,
                'availability_status' => $serviceman->availability_status,
                'is_active' => $serviceman->status === 'active',
                'is_available' => $serviceman->availability_status === 'available',
            ]
        ]);
    }
}
