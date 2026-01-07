<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrahmanPujaPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrahmanPujaPriceController extends Controller
{
    public function index()
    {
        return view('admin.brahman-puja-prices.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = BrahmanPujaPrice::with(['brahman', 'puja']);

        if ($searchValue) {
            $query->whereHas('brahman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhereHas('puja', function($q) use ($searchValue) {
                $q->where('puja_name', 'like', '%' . $searchValue . '%');
            });
        }

        $totalRecords = BrahmanPujaPrice::count();
        $totalRecordswithFilter = $query->count();

        $prices = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($prices as $price) {
            $materialFile = 'No file';
            if ($price->material_file) {
                $materialFile = '<a href="' . asset('storage/' . $price->material_file) . '" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-file"></i> View File</a>';
            }
            
            $data[] = [
                'id' => $price->id,
                'brahman_name' => $price->brahman->name ?? 'N/A',
                'brahman_phone' => $price->brahman->mobile_number ?? 'N/A',
                'puja_name' => $price->puja->puja_name ?? 'N/A',
                'default_price' => $price->puja->price ? '₹' . number_format($price->puja->price, 2) : '₹0.00',
                'price' => '₹' . number_format($price->price, 2),
                'material_file' => $materialFile,
                'action' => '<button class="btn btn-sm btn-primary" onclick="editPrice(' . $price->id . ')"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger" onclick="deletePrice(' . $price->id . ')"><i class="fas fa-trash"></i> Delete</button>',
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
        $price = BrahmanPujaPrice::with(['brahman', 'puja'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $price,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $price = BrahmanPujaPrice::findOrFail($id);
        $data = ['price' => $request->price];

        if ($request->hasFile('material_file')) {
            if ($price->material_file) {
                Storage::disk('public')->delete($price->material_file);
            }
            $data['material_file'] = $request->file('material_file')->store('pujas/materials', 'public');
        }

        $price->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $price = BrahmanPujaPrice::findOrFail($id);
        
        if ($price->material_file) {
            Storage::disk('public')->delete($price->material_file);
        }
        
        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price deleted successfully',
        ]);
    }
}
