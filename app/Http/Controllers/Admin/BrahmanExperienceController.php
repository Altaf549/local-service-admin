<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrahmanExperience;
use Illuminate\Http\Request;

class BrahmanExperienceController extends Controller
{
    public function index()
    {
        return view('admin.brahman-experiences.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = BrahmanExperience::with('brahman');

        if ($searchValue) {
            $query->whereHas('brahman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhere('title', 'like', '%' . $searchValue . '%')
              ->orWhere('organization', 'like', '%' . $searchValue . '%');
        }

        $totalRecords = BrahmanExperience::count();
        $totalRecordswithFilter = $query->count();

        $experiences = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($experiences as $exp) {
            $period = ($exp->start_date ? $exp->start_date->format('Y-m-d') : 'N/A') . ' - ' . 
                     ($exp->is_current ? 'Present' : ($exp->end_date ? $exp->end_date->format('Y-m-d') : 'N/A'));
            
            $data[] = [
                'id' => $exp->id,
                'brahman_name' => $exp->brahman->name ?? 'N/A',
                'brahman_phone' => $exp->brahman->mobile_number ?? 'N/A',
                'title' => $exp->title ?? 'N/A',
                'organization' => $exp->organization ?? 'N/A',
                'years' => $exp->years ?? 'N/A',
                'period' => $period,
                'is_current' => $exp->is_current ? 'Yes' : 'No',
                'action' => '<button class="btn btn-sm btn-primary" onclick="editExperience(' . $exp->id . ')"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteExperience(' . $exp->id . ')"><i class="fas fa-trash"></i> Delete</button>',
            ];
        }

        return response()->json([
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data
        ]);
    }

    public function show($id)
    {
        $experience = BrahmanExperience::with('brahman')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $experience->id,
                'brahman_id' => $experience->brahman_id,
                'title' => $experience->title,
                'description' => $experience->description,
                'years' => $experience->years,
                'organization' => $experience->organization,
                'start_date' => $experience->start_date ? $experience->start_date->format('Y-m-d') : null,
                'end_date' => $experience->end_date ? $experience->end_date->format('Y-m-d') : null,
                'is_current' => $experience->is_current,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'years' => 'nullable|integer|min:0',
            'organization' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $experience = BrahmanExperience::findOrFail($id);
        $experience->update($request->only(['title', 'description', 'years', 'organization', 'start_date', 'end_date', 'is_current']));

        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $experience = BrahmanExperience::findOrFail($id);
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully',
        ]);
    }
}
