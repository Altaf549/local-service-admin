<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrahmanAchievement;
use Illuminate\Http\Request;

class BrahmanAchievementController extends Controller
{
    public function index()
    {
        return view('admin.brahman-achievements.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = BrahmanAchievement::with('brahman');

        if ($searchValue) {
            $query->whereHas('brahman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhere('title', 'like', '%' . $searchValue . '%');
        }

        $totalRecords = BrahmanAchievement::count();
        $totalRecordswithFilter = $query->count();

        $achievements = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($achievements as $ach) {
            $data[] = [
                'id' => $ach->id,
                'brahman_name' => $ach->brahman->name ?? 'N/A',
                'brahman_phone' => $ach->brahman->mobile_number ?? 'N/A',
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
        $achievement = BrahmanAchievement::with('brahman')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $achievement->id,
                'brahman_id' => $achievement->brahman_id,
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

        $achievement = BrahmanAchievement::findOrFail($id);
        $achievement->update($request->only(['title', 'description', 'achieved_date']));

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $achievement = BrahmanAchievement::findOrFail($id);
        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Achievement deleted successfully',
        ]);
    }
}
