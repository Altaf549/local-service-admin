<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicemanExperience;
use Illuminate\Http\Request;

class ServicemanExperienceController extends Controller
{
    public function index()
    {
        return view('admin.serviceman-experiences.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = ServicemanExperience::with('serviceman');

        if ($searchValue) {
            $query->whereHas('serviceman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhere('title', 'like', '%' . $searchValue . '%')
              ->orWhere('company', 'like', '%' . $searchValue . '%');
        }

        $totalRecords = ServicemanExperience::count();
        $totalRecordswithFilter = $query->count();

        $experiences = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($experiences as $exp) {
            $period = ($exp->start_date ? $exp->start_date->format('Y-m-d') : 'N/A') . ' - ' . 
                     ($exp->is_current ? 'Present' : ($exp->end_date ? $exp->end_date->format('Y-m-d') : 'N/A'));
            
            $data[] = [
                'id' => $exp->id,
                'serviceman_name' => $exp->serviceman->name ?? 'N/A',
                'serviceman_phone' => $exp->serviceman->phone ?? 'N/A',
                'title' => $exp->title ?? 'N/A',
                'company' => $exp->company ?? 'N/A',
                'years' => $exp->years ?? 'N/A',
                'period' => $period,
                'is_current' => $exp->is_current ? 'Yes' : 'No',
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
        $experience = ServicemanExperience::with('serviceman')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $experience->id,
                'serviceman_id' => $experience->serviceman_id,
                'title' => $experience->title,
                'description' => $experience->description,
                'years' => $experience->years,
                'company' => $experience->company,
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
            'company' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        $experience = ServicemanExperience::findOrFail($id);
        $experience->update($request->only(['title', 'description', 'years', 'company', 'start_date', 'end_date', 'is_current']));

        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $experience = ServicemanExperience::findOrFail($id);
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully',
        ]);
    }
}
