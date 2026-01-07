<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicemanServicePrice;
use Illuminate\Http\Request;

class ServicemanServicePriceController extends Controller
{
    public function index()
    {
        return view('admin.serviceman-service-prices.index');
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw', 1);
        $start = $request->get("start", 0);
        $rowperpage = $request->get("length", 10);
        $search = $request->get('search', []);
        $searchValue = $search['value'] ?? '';

        $query = ServicemanServicePrice::with(['serviceman', 'service']);

        if ($searchValue) {
            $query->whereHas('serviceman', function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            })->orWhereHas('service', function($q) use ($searchValue) {
                $q->where('service_name', 'like', '%' . $searchValue . '%');
            });
        }

        $totalRecords = ServicemanServicePrice::count();
        $totalRecordswithFilter = $query->count();

        $prices = $query->skip($start)->take($rowperpage)->get();

        $data = [];
        foreach ($prices as $price) {
            $data[] = [
                'id' => $price->id,
                'serviceman_name' => $price->serviceman->name ?? 'N/A',
                'serviceman_phone' => $price->serviceman->phone ?? 'N/A',
                'service_name' => $price->service->service_name ?? 'N/A',
                'default_price' => $price->service->price ? '₹' . number_format($price->service->price, 2) : '₹0.00',
                'price' => '₹' . number_format($price->price, 2),
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
        $price = ServicemanServicePrice::with(['serviceman', 'service'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $price,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $price = ServicemanServicePrice::findOrFail($id);
        $price->update(['price' => $request->price]);

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $price = ServicemanServicePrice::findOrFail($id);
        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price deleted successfully',
        ]);
    }
}
