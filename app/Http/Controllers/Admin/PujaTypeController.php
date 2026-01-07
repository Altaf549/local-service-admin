<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PujaType;
use App\Models\Puja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PujaTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = PujaType::query();

            if ($searchValue) {
                $query->where('type_name', 'like', '%' . $searchValue . '%');
            }

            $totalRecords = PujaType::count();
            $totalRecordswithFilter = $query->count();

            $types = $query->skip($start)->take($rowperpage)->get();

            $data = [];
            foreach ($types as $type) {
                $image = $type->image ? '<img src="' . asset('storage/' . $type->image) . '" width="50" height="50">' : 'No Image';
                $data[] = [
                    'id' => $type->id,
                    'type_name' => $type->type_name,
                    'image' => $image,
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($type->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $type->id . ')"><span class="toggle-slider"></span></label>',
                    'created_at' => $type->created_at->format('Y-m-d H:i:s'),
                    'action' => '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editPujaType(' . $type->id . ')"><i class="fas fa-edit"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deletePujaType(' . $type->id . ')"><i class="fas fa-trash"></i></button>',
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $data
            ]);
        }
        return view('admin.puja-types.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->except(['image', '_method']);
        $data['status'] = $data['status'] ?? 'active';
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('puja-types', 'public');
        }

        PujaType::create($data);

        return response()->json(['success' => 'Puja type created successfully.']);
    }

    public function show($id)
    {
        $type = PujaType::findOrFail($id);
        if ($type->image) {
            $type->image_url = asset('storage/' . $type->image);
        }
        return response()->json($type);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $type = PujaType::findOrFail($id);
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            if ($type->image) {
                Storage::disk('public')->delete($type->image);
            }
            $data['image'] = $request->file('image')->store('puja-types', 'public');
        }

        $oldStatus = $type->status;
        $type->update($data);

        // If puja type is set to inactive, automatically set all related pujas to inactive
        if ($oldStatus === 'active' && $data['status'] === 'inactive') {
            Puja::where('puja_type_id', $type->id)->update(['status' => 'inactive']);
        }

        return response()->json(['success' => 'Puja type updated successfully.']);
    }

    public function destroy($id)
    {
        $type = PujaType::findOrFail($id);
        if ($type->image) {
            Storage::disk('public')->delete($type->image);
        }
        $type->delete();

        return response()->json(['success' => 'Puja type deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        try {
            $type = PujaType::findOrFail($id);
            $oldStatus = $type->status;
            $type->status = $type->status === 'active' ? 'inactive' : 'active';
            $type->save();

            // If puja type is set to inactive, automatically set all related pujas to inactive
            if ($oldStatus === 'active' && $type->status === 'inactive') {
                Puja::where('puja_type_id', $type->id)->update(['status' => 'inactive']);
            }

            return response()->json(['success' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
