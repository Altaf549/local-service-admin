<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Puja;
use App\Models\Brahman;
use Illuminate\Http\Request;

class PujaController extends Controller
{
    public function index()
    {
        $pujas = Puja::where('status', 'active')
            ->with('pujaType')
            ->get()
            ->map(function ($puja) {
                return [
                    'id' => $puja->id,
                    'puja_name' => $puja->puja_name,
                    'puja_type_id' => $puja->puja_type_id,
                    'puja_type_name' => $puja->pujaType->type_name ?? null,
                    'duration' => $puja->duration,
                    'price' => $puja->price,
                    'description' => $puja->description,
                    'image' => $puja->image ? asset('storage/' . $puja->image) : null,
                    'material_file' => $puja->material_file ? asset('storage/' . $puja->material_file) : null,
                    'status' => $puja->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pujas,
        ]);
    }

    public function show($id)
    {
        $puja = Puja::with('pujaType')->findOrFail($id);

        // Get only brahmans who have custom prices for this specific puja
        $brahmanIdsWithPrices = \App\Models\BrahmanPujaPrice::where('puja_id', $id)
            ->pluck('brahman_id')
            ->toArray();

        $brahmans = \App\Models\Brahman::whereIn('id', $brahmanIdsWithPrices)
            ->where('status', 'active')
            ->where('availability_status', 'available')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $puja->id,
                'puja_name' => $puja->puja_name,
                'puja_type' => [
                    'id' => $puja->pujaType->id ?? null,
                    'type_name' => $puja->pujaType->type_name ?? null,
                ],
                'duration' => $puja->duration,
                'price' => $puja->price,
                'description' => $puja->description,
                'image' => $puja->image ? asset('storage/' . $puja->image) : null,
                'material_file' => $puja->material_file ? asset('storage/' . $puja->material_file) : null,
                'status' => $puja->status,
                'brahmans' => $brahmans->map(function ($brahman) use ($puja) {
                    $customPrice = \App\Models\BrahmanPujaPrice::where('brahman_id', $brahman->id)
                        ->where('puja_id', $puja->id)
                        ->first();
                    
                    return [
                        'id' => $brahman->id,
                        'name' => $brahman->name,
                        'specialization' => $brahman->specialization,
                        'languages' => $brahman->languages,
                        'experience' => $brahman->experience,
                        'charges' => $brahman->charges,
                        'mobile_number' => $brahman->mobile_number,
                        'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
                        'availability_status' => $brahman->availability_status,
                        'price' => $customPrice ? $customPrice->price : $puja->price,
                        'custom_price' => $customPrice ? true : false,
                        'material_file' => $customPrice && $customPrice->material_file ? asset('storage/' . $customPrice->material_file) : ($puja->material_file ? asset('storage/' . $puja->material_file) : null),
                    ];
                })->values(),
            ],
        ]);
    }

    public function updatePrice(Request $request, $id)
    {
        // Get authenticated brahman from token
        $user = $request->user();
        
        // Check if the authenticated user is a brahman
        if (!$user instanceof Brahman) {
            return response()->json([
                'success' => false,
                'message' => 'Only brahmans can update puja prices.',
            ], 403);
        }

        // Check if brahman is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. You cannot update puja prices. Please contact support.',
            ], 403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $puja = Puja::findOrFail($id);
        
        // Prepare data for brahman-specific puja price
        $data = ['price' => $request->price];
        
        // Handle material file upload if provided
        if ($request->hasFile('material_file')) {
            // Get existing brahman puja price to check for old material file
            $existingPrice = \App\Models\BrahmanPujaPrice::where('brahman_id', $user->id)
                ->where('puja_id', $id)
                ->first();
            
            // Delete old material file if exists
            if ($existingPrice && $existingPrice->material_file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existingPrice->material_file);
            }
            
            // Store new material file
            $data['material_file'] = $request->file('material_file')->store('pujas/materials', 'public');
        }

        // Update or create brahman-specific puja price
        $brahmanPujaPrice = \App\Models\BrahmanPujaPrice::updateOrCreate(
            [
                'brahman_id' => $user->id,
                'puja_id' => $id,
            ],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Brahman puja price and material file updated successfully',
            'data' => $brahmanPujaPrice->load(['brahman', 'puja']),
        ]);
    }

    public function getPujasByType($typeId)
    {
        $pujas = Puja::with('pujaType')
            ->where('puja_type_id', $typeId)
            ->where('status', 'active')
            ->get()
            ->map(function ($puja) {
                return [
                    'id' => $puja->id,
                    'puja_name' => $puja->puja_name,
                    'puja_type' => $puja->pujaType ? $puja->pujaType->type_name : null,
                    'duration' => $puja->duration,
                    'price' => $puja->price,
                    'description' => $puja->description,
                    'image' => $puja->image ? asset('storage/' . $puja->image) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pujas,
        ]);
    }

    public function getPujaTypes()
    {
        $types = \App\Models\PujaType::where('status', 'active')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'type_name' => $type->type_name,
                    'description' => $type->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
