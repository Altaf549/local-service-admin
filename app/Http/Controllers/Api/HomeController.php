<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\PujaType;
use App\Models\Puja;
use App\Models\Serviceman;
use App\Models\Brahman;

class HomeController extends Controller
{
    public function index()
    {
        // Banners
        $banners = Banner::where('status', 'active')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? null,
                    'image' => $banner->image ? asset('storage/' . $banner->image) : null,
                    'status' => $banner->status,
                ];
            });

        // Service Categories (max 5)
        $serviceCategories = ServiceCategory::where('status', 'active')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                ];
            });

        // Services (max 5)
        $services = Service::where('status', 'active')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'category_name' => $service->category->category_name ?? null,
                    'price' => $service->price,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                ];
            });

        // Servicemen (max 5)
        $servicemen = Serviceman::where('status', 'active')
            ->where('availability_status', 'available')
            ->limit(5)
            ->get()
            ->map(function ($serviceman) {
                return [
                    'id' => $serviceman->id,
                    'name' => $serviceman->name,
                    'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
                ];
            });

        // Puja Types (max 5)
        $pujaTypes = PujaType::where('status', 'active')
            ->limit(5)
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'type_name' => $type->type_name,
                    'image' => $type->image ? asset('storage/' . $type->image) : null,
                ];
            });

        // Pujas (max 5)
        $pujas = Puja::where('status', 'active')
            ->limit(5)
            ->with('pujaType')
            ->get()
            ->map(function ($puja) {
                return [
                    'id' => $puja->id,
                    'puja_name' => $puja->puja_name,
                    'puja_type_name' => $puja->pujaType->type_name ?? null,
                    'price' => $puja->price,
                    'image' => $puja->image ? asset('storage/' . $puja->image) : null,
                ];
            });

        // Brahmans (max 5)
        $brahmans = Brahman::where('status', 'active')
            ->where('availability_status', 'available')
            ->limit(5)
            ->get()
            ->map(function ($brahman) {
                return [
                    'id' => $brahman->id,
                    'name' => $brahman->name,
                    'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'banners' => $banners,
                'service_categories' => $serviceCategories,
                'services' => $services,
                'servicemen' => $servicemen,
                'puja_types' => $pujaTypes,
                'pujas' => $pujas,
                'brahmans' => $brahmans,
            ],
        ]);
    }
}
