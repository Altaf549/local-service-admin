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
                'message' => 'An error occurred: ' . $e->getMessage(),
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
}
