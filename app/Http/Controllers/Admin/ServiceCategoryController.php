<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = ServiceCategory::query();

            if ($searchValue) {
                $query->where('category_name', 'like', '%' . $searchValue . '%');
            }

            $totalRecords = ServiceCategory::count();
            $totalRecordswithFilter = $query->count();

            $categories = $query->skip($start)
                ->take($rowperpage)
                ->get();

            $data = [];
            foreach ($categories as $category) {
                $image = $category->image ? '<img src="' . asset('storage/' . $category->image) . '" width="50" height="50">' : 'No Image';
                $data[] = [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'image' => $image,
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($category->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $category->id . ')"><span class="toggle-slider"></span></label>',
                    'created_at' => $category->created_at->format('Y-m-d H:i:s'),
                    'action' => '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editCategory(' . $category->id . ')"><i class="fas fa-edit"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory(' . $category->id . ')"><i class="fas fa-trash"></i></button>',
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $data
            ]);
        }
        return view('admin.service-categories.index');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'nullable|in:active,inactive',
            ]);

            $data = $request->except(['image', '_method']);
            $data['status'] = $data['status'] ?? 'active';
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('service-categories', 'public');
            }

            ServiceCategory::create($data);

            return response()->json(['success' => 'Category created successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $category = ServiceCategory::findOrFail($id);
        if ($category->image) {
            $category->image_url = asset('storage/' . $category->image);
        }
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'nullable|in:active,inactive',
            ]);

            $category = ServiceCategory::findOrFail($id);
            $data = $request->except(['image', '_method']);

            if ($request->hasFile('image')) {
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $data['image'] = $request->file('image')->store('service-categories', 'public');
            }

            $oldStatus = $category->status;
            $category->update($data);

            // If category is set to inactive, automatically set all related services to inactive
            if ($oldStatus === 'active' && $data['status'] === 'inactive') {
                Service::where('category_id', $category->id)->update(['status' => 'inactive']);
            }

            return response()->json(['success' => 'Category updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $category = ServiceCategory::findOrFail($id);
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return response()->json(['success' => 'Category deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        try {
            $category = ServiceCategory::findOrFail($id);
            $oldStatus = $category->status;
            $category->status = $category->status === 'active' ? 'inactive' : 'active';
            $category->save();

            // If category is set to inactive, automatically set all related services to inactive
            if ($oldStatus === 'active' && $category->status === 'inactive') {
                Service::where('category_id', $category->id)->update(['status' => 'inactive']);
            }

            return response()->json(['success' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
