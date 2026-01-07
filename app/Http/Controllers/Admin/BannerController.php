<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $searchValue = $request->get('search')['value'] ?? '';

            $query = Banner::query();

            if ($searchValue) {
                $query->where('title', 'like', '%' . $searchValue . '%');
            }

            $totalRecords = Banner::count();
            $totalRecordswithFilter = $query->count();

            $banners = $query->orderBy('order')->skip($start)->take($rowperpage)->get();

            $data = [];
            foreach ($banners as $banner) {
                $image = $banner->image ? '<img src="' . asset('storage/' . $banner->image) . '" width="100" height="50">' : 'No Image';
                $data[] = [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'N/A',
                    'image' => $image,
                    'link' => $banner->link ?? 'N/A',
                    'order' => $banner->order,
                    'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($banner->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $banner->id . ')"><span class="toggle-slider"></span></label>',
                    'action' => '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editBanner(' . $banner->id . ')"><i class="fas fa-edit"></i></button>' .
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteBanner(' . $banner->id . ')"><i class="fas fa-trash"></i></button>',
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $data
            ]);
        }
        return view('admin.banners.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->except(['image', '_method']);
        $data['status'] = $data['status'] ?? 'active';
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        Banner::create($data);

        return response()->json(['success' => 'Banner created successfully.']);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        if ($banner->image) {
            $banner->image_url = asset('storage/' . $banner->image);
        }
        return response()->json($banner);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $banner = Banner::findOrFail($id);
        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return response()->json(['success' => 'Banner updated successfully.']);
    }

    public function toggleStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $banner->status === 'active' ? 'inactive' : 'active';
        $banner->save();

        return response()->json(['success' => 'Status updated successfully.']);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return response()->json(['success' => 'Banner deleted successfully.']);
    }
}
