<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicemanAchievement;
use Illuminate\Http\Request;

class ServicemanAchievementController extends Controller
{
    public function index()
    {
        return view('admin.serviceman-achievements.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = ServicemanAchievement::with('serviceman');

        if ($searchValue) {
            $query->whereHas('serviceman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhere('title', 'like', '%' . $searchValue . '%');
        }

        $totalRecords = ServicemanAchievement::count();
        $totalRecordswithFilter = $query->count();

        $achievements = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($achievements as $ach) {
            $data[] = [
                'id' => $ach->id,
                'serviceman_name' => $ach->serviceman->name ?? 'N/A',
                'serviceman_phone' => $ach->serviceman->phone ?? 'N/A',
                'title' => $ach->title ?? 'N/A',
                'description' => $ach->description ? substr($ach->description, 0, 50) . '...' : 'N/A',
                'achieved_date' => $ach->achieved_date ? $ach->achieved_date->format('Y-m-d') : 'N/A',
                'action' => '<button class="btn btn-sm btn-primary" onclick="editAchievement(' . $ach->id . ')"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteAchievement(' . $ach->id . ')"><i class="fas fa-trash"></i> Delete</button>',
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
        $achievement = ServicemanAchievement::with('serviceman')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $achievement->id,
                'serviceman_id' => $achievement->serviceman_id,
                'title' => $achievement->title,
                'description' => $achievement->description,
                'achieved_date' => $achievement->achieved_date ? $achievement->achieved_date->format('Y-m-d') : null,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'achieved_date' => 'nullable|date',
        ]);

        $achievement = ServicemanAchievement::findOrFail($id);
        $achievement->update($request->only(['title', 'description', 'achieved_date']));

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $achievement = ServicemanAchievement::findOrFail($id);
        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Achievement deleted successfully',
        ]);
    }
}
