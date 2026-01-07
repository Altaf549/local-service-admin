<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::where('status', 'active')
            ->with('services')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'status' => $category->status,
                    'services_count' => $category->services->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = ServiceCategory::with('services')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'image' => $category->image ? asset('storage/' . $category->image) : null,
                'status' => $category->status,
                'services' => $category->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_name' => $service->service_name,
                        'price' => $service->price,
                        'description' => $service->description,
                        'image' => $service->image ? asset('storage/' . $service->image) : null,
                        'status' => $service->status,
                    ];
                }),
            ],
        ]);
    }
}
