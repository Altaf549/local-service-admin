<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PujaType;
use Illuminate\Http\Request;

class PujaTypeController extends Controller
{
    public function index()
    {
        $pujaTypes = PujaType::where('status', 'active')
            ->with('pujas')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'type_name' => $type->type_name,
                    'image' => $type->image ? asset('storage/' . $type->image) : null,
                    'status' => $type->status,
                    'pujas_count' => $type->pujas->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pujaTypes,
        ]);
    }

    public function show($id)
    {
        $pujaType = PujaType::with('pujas')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pujaType->id,
                'type_name' => $pujaType->type_name,
                'image' => $pujaType->image ? asset('storage/' . $pujaType->image) : null,
                'status' => $pujaType->status,
                'pujas' => $pujaType->pujas->map(function ($puja) {
                    return [
                        'id' => $puja->id,
                        'puja_name' => $puja->puja_name,
                        'duration' => $puja->duration,
                        'price' => $puja->price,
                        'description' => $puja->description,
                        'image' => $puja->image ? asset('storage/' . $puja->image) : null,
                        'status' => $puja->status,
                    ];
                }),
            ],
        ]);
    }
}
