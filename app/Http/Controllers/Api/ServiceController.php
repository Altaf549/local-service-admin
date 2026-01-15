<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Serviceman;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('status', 'active')
            ->with('category')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'category_id' => $service->category_id,
                    'category_name' => $service->category->category_name ?? null,
                    'price' => $service->price,
                    'description' => $service->description,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                    'status' => $service->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show($id)
    {
        $service = Service::with('category')->findOrFail($id);

        // Get servicemen from service_serviceman pivot table
        $servicemenFromPivot = $service->servicemen()
            ->where('status', 'active')
            ->where('availability_status', 'available')
            ->get();

        // Get servicemen who have custom prices in serviceman_service_prices table
        $servicemanIdsWithPrices = \App\Models\ServicemanServicePrice::where('service_id', $id)
            ->pluck('serviceman_id')
            ->toArray();

        $servicemenWithPrices = \App\Models\Serviceman::whereIn('id', $servicemanIdsWithPrices)
            ->where('status', 'active')
            ->where('availability_status', 'available')
            ->get();

        // Merge both collections and remove duplicates by ID
        $allServicemen = $servicemenFromPivot->merge($servicemenWithPrices)->unique('id');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'category' => [
                    'id' => $service->category->id ?? null,
                    'category_name' => $service->category->category_name ?? null,
                ],
                'price' => $service->price,
                'description' => $service->description,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'status' => $service->status,
                'servicemen' => $allServicemen->map(function ($serviceman) use ($service) {
                    $customPrice = \App\Models\ServicemanServicePrice::where('serviceman_id', $serviceman->id)
                        ->where('service_id', $service->id)
                        ->first();
                    
                    return [
                        'id' => $serviceman->id,
                        'name' => $serviceman->name,
                        'phone' => $serviceman->phone,
                        'experience' => $serviceman->experience,
                        'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
                        'availability_status' => $serviceman->availability_status,
                        'price' => $customPrice ? $customPrice->price : $service->price,
                        'custom_price' => $customPrice ? true : false,
                    ];
                })->values(),
            ],
        ]);
    }

    public function addPrice(Request $request, $id)
    {
        try {
            // Get authenticated serviceman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if the authenticated user is a serviceman
            if (!$user instanceof Serviceman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only servicemen can update service prices.',
                ], 403);
            }

            // Check if serviceman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot update service prices. Please contact support.',
                ], 403);
            }

            // Validate the price - Laravel should automatically parse JSON if Content-Type is application/json
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
            ]);

            $servicemanServicePrice = \App\Models\ServicemanServicePrice::updateOrCreate(
                [
                    'serviceman_id' => $user->id,
                    'service_id' => $id,
                ],
                [
                    'price' => $validated['price'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Serviceman service price updated successfully',
                'data' => $servicemanServicePrice->load(['serviceman', 'service']),
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
                'message' => 'Failed to update service price: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getServicesByCategory($categoryId)
    {
        $services = Service::with('category')
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'category' => [
                        'id' => $service->category->id,
                        'category_name' => $service->category->category_name,
                    ],
                    'price' => $service->price,
                    'description' => $service->description,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function getAllPrices(Request $request)
    {
        try {
            // Get authenticated serviceman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a serviceman
            if (!$user instanceof Serviceman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only servicemen can view their service prices.',
                ], 403);
            }

            // Check if serviceman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot view service prices. Please contact support.',
                ], 403);
            }

            // Get all service prices for this serviceman
            $prices = \App\Models\ServicemanServicePrice::with(['serviceman', 'service.category'])
                ->where('serviceman_id', $user->id)
                ->get()
                ->map(function ($price) {
                    return [
                        'id' => $price->id,
                        'serviceman_id' => $price->serviceman_id,
                        'serviceman_name' => $price->serviceman->name ?? null,
                        'service_id' => $price->service_id,
                        'service_name' => $price->service->service_name,
                        'category' => $price->service->category ? [
                            'id' => $price->service->category->id,
                            'category_name' => $price->service->category->category_name,
                        ] : null,
                        'price' => $price->price,
                        'description' => $price->service->description,
                        'image' => $price->service->image ? asset('storage/' . $price->service->image) : null,
                        'created_at' => $price->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $price->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Service prices retrieved successfully',
                'data' => $prices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service prices: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPrice(Request $request, $id)
    {
        try {
            // Get authenticated serviceman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a serviceman
            if (!$user instanceof Serviceman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only servicemen can view their service prices.',
                ], 403);
            }

            // Check if serviceman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot view service prices. Please contact support.',
                ], 403);
            }

            // Get specific service price for this serviceman
            $price = \App\Models\ServicemanServicePrice::with(['serviceman', 'service.category'])
                ->where('serviceman_id', $user->id)
                ->where('service_id', $id)
                ->first();

            if (!$price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service price not found',
                    'errors' => [
                        'service_id' => ['Service price not found for this service']
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service price retrieved successfully',
                'data' => [
                    'id' => $price->id,
                    'serviceman_id' => $price->serviceman_id,
                    'serviceman_name' => $price->serviceman->name ?? null,
                    'service_id' => $price->service_id,
                    'service_name' => $price->service->service_name,
                    'category' => $price->service->category ? [
                        'id' => $price->service->category->id,
                        'category_name' => $price->service->category->category_name,
                    ] : null,
                    'price' => $price->price,
                    'description' => $price->service->description,
                    'image' => $price->service->image ? asset('storage/' . $price->service->image) : null,
                    'created_at' => $price->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $price->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service price: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updatePrice(Request $request, $id)
    {
        try {
            // Get authenticated serviceman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a serviceman
            if (!$user instanceof Serviceman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only servicemen can update service prices.',
                ], 403);
            }

            // Check if serviceman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot update service prices. Please contact support.',
                ], 403);
            }

            // Validate price
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
            ]);

            // Find existing service price
            $price = \App\Models\ServicemanServicePrice::where('serviceman_id', $user->id)
                ->where('service_id', $id)
                ->first();

            if (!$price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service price not found',
                    'errors' => [
                        'service_id' => ['Service price not found for this service']
                    ]
                ], 404);
            }

            // Update price
            $price->price = $validated['price'];
            $price->save();

            return response()->json([
                'success' => true,
                'message' => 'Service price updated successfully',
                'data' => $price->load(['serviceman', 'service']),
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
                'message' => 'Failed to update service price: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deletePrice(Request $request, $id)
    {
        try {
            // Get authenticated serviceman from token
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }
            
            // Check if authenticated user is a serviceman
            if (!$user instanceof Serviceman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only servicemen can delete service prices.',
                ], 403);
            }

            // Check if serviceman is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. You cannot delete service prices. Please contact support.',
                ], 403);
            }

            // Find and delete service price
            $price = \App\Models\ServicemanServicePrice::where('serviceman_id', $user->id)
                ->where('service_id', $id)
                ->first();

            if (!$price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service price not found',
                    'errors' => [
                        'service_id' => ['Service price not found for this service']
                    ]
                ], 404);
            }

            $price->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service price deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service price: ' . $e->getMessage(),
            ], 500);
        }
    }
}
