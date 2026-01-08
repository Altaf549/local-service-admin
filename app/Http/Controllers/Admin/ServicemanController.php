<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Serviceman;
use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicemanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = Serviceman::with('category');

            if ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                      ->orWhere('phone', 'like', '%' . $searchValue . '%')
                      ->orWhereHas('category', function($q) use ($searchValue) {
                          $q->where('category_name', 'like', '%' . $searchValue . '%');
                      });
            }

            $totalRecords = Serviceman::count();
            $totalRecordswithFilter = $query->count();

            $servicemen = $query->skip($start)->take($rowperpage)->get();

            $data = [];
            foreach ($servicemen as $serviceman) {
                $data[] = [
                    'id' => $serviceman->id,
                    'name' => $serviceman->name,
                    'phone' => $serviceman->phone,
                    'category' => $serviceman->category->category_name ?? 'N/A',
                    'experience' => $serviceman->experience ? $serviceman->experience . ' years' : 'N/A',
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($serviceman->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $serviceman->id . ')"><span class="toggle-slider"></span></label>',
                    'action' => '<button type="button" class="btn btn-sm btn-info me-1" onclick="viewDetails(' . $serviceman->id . ')"><i class="fas fa-eye"></i></button>',
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
        $services = Service::where('status', 'active')->get();
        return view('admin.servicemen.index', compact('categories', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'service_category' => 'required|exists:service_categories,id',
            'experience' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:available,unavailable',
        ]);

        Serviceman::create($request->all());

        return response()->json(['success' => 'Serviceman created successfully.']);
    }

    public function show($id)
    {
        $serviceman = Serviceman::with(['category', 'services', 'experiences', 'achievements'])->findOrFail($id);
        
        // Format the data for display
        $data = [
            'id' => $serviceman->id,
            'name' => $serviceman->name,
            'email' => $serviceman->email ?? 'N/A',
            'mobile_number' => $serviceman->mobile_number ?? 'N/A',
            'phone' => $serviceman->phone,
            'category' => $serviceman->category->category_name ?? 'N/A',
            'experience' => $serviceman->experience ? $serviceman->experience . ' years' : 'N/A',
            'status' => $serviceman->status,
            'availability_status' => $serviceman->availability_status,
            'government_id' => $serviceman->government_id ?? 'N/A',
            'address' => $serviceman->address ?? 'N/A',
            'profile_photo' => $serviceman->profile_photo ? asset('storage/' . $serviceman->profile_photo) : null,
            'id_proof_image' => $serviceman->id_proof_image ? asset('storage/' . $serviceman->id_proof_image) : null,
            'created_at' => $serviceman->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $serviceman->updated_at->format('Y-m-d H:i:s'),
        ];
        
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'service_category' => 'required|exists:service_categories,id',
            'experience' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:available,unavailable',
        ]);

        $serviceman = Serviceman::findOrFail($id);
        $serviceman->update($request->all());

        return response()->json(['success' => 'Serviceman updated successfully.']);
    }

    public function toggleStatus($id)
    {
        $serviceman = Serviceman::findOrFail($id);
        $serviceman->status = $serviceman->status === 'active' ? 'inactive' : 'active';
        $serviceman->save();

        return response()->json(['success' => 'Status updated successfully.']);
    }

    public function assignServices(Request $request, $id)
    {
        $serviceman = Serviceman::findOrFail($id);
        $serviceman->services()->sync($request->service_ids ?? []);

        return response()->json(['success' => 'Services assigned successfully.']);
    }

    public function destroy($id)
    {
        $serviceman = Serviceman::findOrFail($id);
        $serviceman->delete();

        return response()->json(['success' => 'Serviceman deleted successfully.']);
    }
}
