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

    public function addPrice(Request $request, $id)
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
        
        // Check if price already exists for this brahman and puja
        $existingPrice = \App\Models\BrahmanPujaPrice::where('brahman_id', $user->id)
            ->where('puja_id', $id)
            ->first();

        if ($existingPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Price already added for this puja',
                'errors' => [
                    'puja_id' => ['Price already exists for this puja. Use update endpoint to modify.']
                ]
            ], 422);
        }
        
        // Prepare data for brahman-specific puja price
        $data = [
            'brahman_id' => $user->id,
            'puja_id' => $id,
            'price' => $request->price
        ];
        
        // Handle material file upload if provided
        if ($request->hasFile('material_file')) {
            // Store new material file
            $data['material_file'] = $request->file('material_file')->store('pujas/materials', 'public');
        }

        // Create new brahman-specific puja price
        $brahmanPujaPrice = \App\Models\BrahmanPujaPrice::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Brahman puja price and material file updated successfully',
            'data' => $brahmanPujaPrice->load(['brahman', 'puja']),
        ]);
    }

    // Get All Puja Prices for Brahman
    public function getAllPrices(Request $request)
    {
        try {
            // Get authenticated brahman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a brahman
            if (!$user instanceof Brahman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only brahmans can view their puja prices.',
                ], 403);
            }

            // Check if brahman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot view puja prices. Please contact support.',
                ], 403);
            }

            // Get all puja prices for this brahman
            $pujaPrices = \App\Models\BrahmanPujaPrice::with('puja.pujaType')
                ->where('brahman_id', $user->id)
                ->get()
                ->map(function ($price) {
                    return [
                        'id' => $price->id,
                        'puja_id' => $price->puja_id,
                        'puja_name' => $price->puja->puja_name,
                        'puja_type' => $price->puja->pujaType ? [
                            'id' => $price->puja->pujaType->id,
                            'type_name' => $price->puja->pujaType->type_name,
                        ] : null,
                        'duration' => $price->puja->duration,
                        'price' => $price->price,
                        'description' => $price->puja->description,
                        'image' => $price->puja->image ? asset('storage/' . $price->puja->image) : null,
                        'material_file' => $price->material_file ? asset('storage/' . $price->material_file) : null,
                        'material_file_url' => $price->material_file ? url('storage/' . $price->material_file) : null,
                        'created_at' => $price->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $price->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Puja prices retrieved successfully',
                'data' => $pujaPrices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve puja prices: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Get Single Puja Price for Brahman
    public function getPrice(Request $request, $id)
    {
        try {
            // Get authenticated brahman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a brahman
            if (!$user instanceof Brahman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only brahmans can view their puja prices.',
                ], 403);
            }

            // Check if brahman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot view puja prices. Please contact support.',
                ], 403);
            }

            // Get specific puja price for this brahman
            $pujaPrice = \App\Models\BrahmanPujaPrice::with('puja.pujaType')
                ->where('brahman_id', $user->id)
                ->where('puja_id', $id)
                ->first();

            if (!$pujaPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puja price not found',
                    'errors' => [
                        'puja_id' => ['Puja price not found for this puja']
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Puja price retrieved successfully',
                'data' => [
                    'id' => $pujaPrice->id,
                    'puja_id' => $pujaPrice->puja_id,
                    'puja_name' => $pujaPrice->puja->puja_name,
                    'puja_type' => $pujaPrice->puja->pujaType ? [
                        'id' => $pujaPrice->puja->pujaType->id,
                        'type_name' => $pujaPrice->puja->pujaType->type_name,
                    ] : null,
                    'duration' => $pujaPrice->puja->duration,
                    'price' => $pujaPrice->price,
                    'description' => $pujaPrice->puja->description,
                    'image' => $pujaPrice->puja->image ? asset('storage/' . $pujaPrice->puja->image) : null,
                    'material_file' => $pujaPrice->material_file ? asset('storage/' . $pujaPrice->material_file) : null,
                    'material_file_url' => $pujaPrice->material_file ? url('storage/' . $pujaPrice->material_file) : null,
                    'created_at' => $pujaPrice->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $pujaPrice->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve puja price: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Update Puja Price for Brahman
    public function updatePrice(Request $request, $id)
    {
        try {
            // Get authenticated brahman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a brahman
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

            // Validate price
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            ]);

            // Find the brahman puja price record by ID
            $pujaPrice = \App\Models\BrahmanPujaPrice::where('id', $id)
                ->where('brahman_id', $user->id)
                ->first();

            if (!$pujaPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puja price not found',
                    'errors' => [
                        'id' => ['Puja price not found or you do not have permission to update it']
                    ]
                ], 404);
            }

            // Prepare data for brahman-specific puja price
            $data = ['price' => $validated['price']];
            
            // Handle material file upload if provided
            if ($request->hasFile('material_file')) {
                // Delete old material file if exists
                if ($pujaPrice->material_file) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($pujaPrice->material_file);
                }
                
                // Store new material file
                $data['material_file'] = $request->file('material_file')->store('pujas/materials', 'public');
            }

            // Update the brahman puja price
            $pujaPrice->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Brahman puja price updated successfully',
                'data' => $pujaPrice->load(['brahman', 'puja']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update puja price: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete Puja Price for Brahman
    public function deletePrice(Request $request, $id)
    {
        try {
            // Get authenticated brahman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a brahman
            if (!$user instanceof Brahman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only brahmans can delete puja prices.',
                ], 403);
            }

            // Check if brahman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot delete puja prices. Please contact support.',
                ], 403);
            }

            // Find the brahman puja price record by ID
            $pujaPrice = \App\Models\BrahmanPujaPrice::where('id', $id)
                ->where('brahman_id', $user->id)
                ->first();

            if (!$pujaPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puja price not found',
                    'errors' => [
                        'id' => ['Puja price not found or you do not have permission to delete it']
                    ]
                ], 404);
            }

            $pujaPrice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Puja price deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete puja price: ' . $e->getMessage(),
            ], 500);
        }
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
