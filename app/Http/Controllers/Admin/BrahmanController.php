<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brahman;
use Illuminate\Http\Request;

class BrahmanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $draw = $request->get('draw', 1);
                $start = $request->get("start", 0);
                $rowperpage = $request->get("length", 10);
                $search = $request->get('search', []);
                $searchValue = $search['value'] ?? '';

                $query = Brahman::query();

                if ($searchValue) {
                    $query->where(function($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%')
                          ->orWhere('specialization', 'like', '%' . $searchValue . '%')
                          ->orWhere('languages', 'like', '%' . $searchValue . '%');
                    });
                }

                $totalRecords = Brahman::count();
                $totalRecordswithFilter = $query->count();

                $brahmans = $query->skip($start)->take($rowperpage)->get();

                $data = [];
                foreach ($brahmans as $brahman) {
                    $data[] = [
                        'id' => $brahman->id,
                        'name' => $brahman->name ?? '',
                        'specialization' => $brahman->specialization ?? 'N/A',
                        'languages' => $brahman->languages ?? 'N/A',
                        'experience' => $brahman->experience ? $brahman->experience . ' years' : 'N/A',
                        'charges' => $brahman->charges ? '₹' . number_format($brahman->charges, 2) : 'N/A',
                        'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($brahman->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $brahman->id . ')"><span class="toggle-slider"></span></label>',
                        'action' => '<button type="button" class="btn btn-sm btn-info" onclick="viewDetails(' . $brahman->id . ')"><i class="fas fa-eye"></i></button>',
                    ];
                }

                return response()->json([
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecords,
                    "iTotalDisplayRecords" => $totalRecordswithFilter,
                    "aaData" => $data
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "draw" => intval($request->get('draw', 1)),
                    "iTotalRecords" => 0,
                    "iTotalDisplayRecords" => 0,
                    "aaData" => [],
                    "error" => $e->getMessage()
                ], 500);
            }
        }
        return view('admin.brahmans.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'experience' => 'nullable|integer|min:0',
            'charges' => 'nullable|numeric|min:0',
            'availability_status' => 'required|in:available,unavailable',
        ]);

        Brahman::create($request->all());

        return response()->json(['success' => 'Brahman created successfully.']);
    }

    public function show($id)
    {
        $brahman = Brahman::with(['experiences', 'achievements'])->findOrFail($id);
        
        // Format the data for display
        $data = [
            'id' => $brahman->id,
            'name' => $brahman->name,
            'email' => $brahman->email ?? 'N/A',
            'mobile_number' => $brahman->mobile_number ?? 'N/A',
            'specialization' => $brahman->specialization ?? 'N/A',
            'languages' => $brahman->languages ?? 'N/A',
            'experience' => $brahman->experience ? $brahman->experience . ' years' : 'N/A',
            'charges' => $brahman->charges ? '₹' . number_format($brahman->charges, 2) : 'N/A',
            'status' => $brahman->status,
            'availability_status' => $brahman->availability_status,
            'government_id' => $brahman->government_id ?? 'N/A',
            'address' => $brahman->address ?? 'N/A',
            'profile_photo' => $brahman->profile_photo ? asset('storage/' . $brahman->profile_photo) : null,
            'id_proof_image' => $brahman->id_proof_image ? asset('storage/' . $brahman->id_proof_image) : null,
            'experiences' => $brahman->experiences->map(function($exp) {
                return [
                    'id' => $exp->id,
                    'title' => $exp->title,
                    'description' => $exp->description,
                    'years' => $exp->years,
                    'organization' => $exp->organization,
                    'start_date' => $exp->start_date ? $exp->start_date->format('Y-m-d') : null,
                    'end_date' => $exp->end_date ? $exp->end_date->format('Y-m-d') : null,
                    'is_current' => $exp->is_current,
                ];
            }),
            'achievements' => $brahman->achievements->map(function($ach) {
                return [
                    'id' => $ach->id,
                    'title' => $ach->title,
                    'description' => $ach->description,
                    'achieved_date' => $ach->achieved_date ? $ach->achieved_date->format('Y-m-d') : null,
                ];
            }),
            'created_at' => $brahman->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $brahman->updated_at->format('Y-m-d H:i:s'),
        ];
        
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'experience' => 'nullable|integer|min:0',
            'charges' => 'nullable|numeric|min:0',
            'availability_status' => 'required|in:available,unavailable',
        ]);

        $brahman = Brahman::findOrFail($id);
        $brahman->update($request->all());

        return response()->json(['success' => 'Brahman updated successfully.']);
    }

    public function toggleStatus($id)
    {
        $brahman = Brahman::findOrFail($id);
        $brahman->status = $brahman->status === 'active' ? 'inactive' : 'active';
        $brahman->save();

        return response()->json(['success' => 'Status updated successfully.']);
    }

    public function destroy($id)
    {
        $brahman = Brahman::findOrFail($id);
        $brahman->delete();

        return response()->json(['success' => 'Brahman deleted successfully.']);
    }
}
