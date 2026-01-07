<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = Service::with('category');

            if ($searchValue) {
                $query->where('service_name', 'like', '%' . $searchValue . '%')
                      ->orWhereHas('category', function($q) use ($searchValue) {
                          $q->where('category_name', 'like', '%' . $searchValue . '%');
                      });
            }

            $totalRecords = Service::count();
            $totalRecordswithFilter = $query->count();

            $services = $query->skip($start)->take($rowperpage)->get();

            $data = [];
            foreach ($services as $service) {
                $image = $service->image ? '<img src="' . asset('storage/' . $service->image) . '" width="50" height="50">' : 'No Image';
                $data[] = [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'category' => $service->category->category_name ?? 'N/A',
                    'price' => '₹' . number_format($service->price, 2),
                    'image' => $image,
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($service->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $service->id . ')"><span class="toggle-slider"></span></label>',
                    'action' => '<button type="button" class="btn btn-sm btn-info me-1" onclick="viewService(' . $service->id . ')"><i class="fas fa-eye"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editService(' . $service->id . ')"><i class="fas fa-edit"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteService(' . $service->id . ')"><i class="fas fa-trash"></i></button>',
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $data
            ]);
        }

        $categories = ServiceCategory::where('status', 'active')->get();
        return view('admin.services.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->except(['image', '_method']);
        $data['status'] = $data['status'] ?? 'active';
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($data);

        return response()->json(['success' => 'Service created successfully.']);
    }

    public function show($id)
    {
        $service = Service::with('category')->findOrFail($id);
        if ($service->image) {
            $service->image_url = asset('storage/' . $service->image);
        }
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $service = Service::findOrFail($id);
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return response()->json(['success' => 'Service updated successfully.']);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();

        return response()->json(['success' => 'Service deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->status = $service->status === 'active' ? 'inactive' : 'active';
            $service->save();

            return response()->json(['success' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
