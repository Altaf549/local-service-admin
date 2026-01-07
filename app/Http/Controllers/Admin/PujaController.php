<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Puja;
use App\Models\PujaType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PujaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = Puja::with('pujaType');

            if ($searchValue) {
                $query->where('puja_name', 'like', '%' . $searchValue . '%')
                      ->orWhereHas('pujaType', function($q) use ($searchValue) {
                          $q->where('type_name', 'like', '%' . $searchValue . '%');
                      });
            }

            $totalRecords = Puja::count();
            $totalRecordswithFilter = $query->count();

            $pujas = $query->skip($start)->take($rowperpage)->get();

            $data = [];
            foreach ($pujas as $puja) {
                $image = $puja->image ? '<img src="' . asset('storage/' . $puja->image) . '" width="50" height="50">' : 'No Image';
                $data[] = [
                    'id' => $puja->id,
                    'puja_name' => $puja->puja_name,
                    'type' => $puja->pujaType->type_name ?? 'N/A',
                    'duration' => $puja->duration ?? 'N/A',
                    'price' => '₹' . number_format($puja->price, 2),
                    'image' => $image,
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($puja->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $puja->id . ')"><span class="toggle-slider"></span></label>',
                    'action' => '<button type="button" class="btn btn-sm btn-info me-1" onclick="viewPuja(' . $puja->id . ')"><i class="fas fa-eye"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editPuja(' . $puja->id . ')"><i class="fas fa-edit"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deletePuja(' . $puja->id . ')"><i class="fas fa-trash"></i></button>',
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $data
            ]);
        }

        $types = PujaType::where('status', 'active')->get();
        return view('admin.pujas.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'puja_name' => 'required|string|max:255',
            'puja_type_id' => 'required|exists:puja_types,id',
            'duration' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->except(['image', '_method']);
        $data['status'] = $data['status'] ?? 'active';
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pujas', 'public');
        }

        Puja::create($data);

        return response()->json(['success' => 'Puja created successfully.']);
    }

    public function show($id)
    {
        $puja = Puja::with('pujaType')->findOrFail($id);
        if ($puja->image) {
            $puja->image_url = asset('storage/' . $puja->image);
        }
        return response()->json($puja);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'puja_name' => 'required|string|max:255',
            'puja_type_id' => 'required|exists:puja_types,id',
            'duration' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $puja = Puja::findOrFail($id);
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            if ($puja->image) {
                Storage::disk('public')->delete($puja->image);
            }
            $data['image'] = $request->file('image')->store('pujas', 'public');
        }

        $puja->update($data);

        return response()->json(['success' => 'Puja updated successfully.']);
    }

    public function destroy($id)
    {
        $puja = Puja::findOrFail($id);
        if ($puja->image) {
            Storage::disk('public')->delete($puja->image);
        }
        $puja->delete();

        return response()->json(['success' => 'Puja deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        try {
            $puja = Puja::findOrFail($id);
            $puja->status = $puja->status === 'active' ? 'inactive' : 'active';
            $puja->save();

            return response()->json(['success' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
