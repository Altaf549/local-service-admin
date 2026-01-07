<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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

                $query = User::query();
                
                // Only show app users (role = 'user'), exclude admin users
                $query->where('role', '!=', 'admin');

                if ($searchValue) {
                    $query->where(function($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%')
                          ->orWhere('email', 'like', '%' . $searchValue . '%');
                    });
                }

                $totalRecords = User::where('role', '!=', 'admin')->count();
                $totalRecordswithFilter = $query->count();

                $users = $query->skip($start)->take($rowperpage)->get();

                $data = [];
                foreach ($users as $user) {
                    $data[] = [
                        'id' => $user->id,
                        'name' => $user->name ?? '',
                        'email' => $user->email ?? '',
                        'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'Not Verified',
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        'status' => '<label class="toggle-switch"><input type="checkbox" ' . ($user->status === 'active' ? 'checked' : '') . ' onchange="toggleStatus(' . $user->id . ')"><span class="toggle-slider"></span></label>',
                        'action' => '<button type="button" class="btn btn-sm btn-info" onclick="viewDetails(' . $user->id . ')"><i class="fas fa-eye"></i></button> <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(' . $user->id . ')"><i class="fas fa-trash"></i></button>',
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
                    "aaData" => []
                ], 500);
            }
        }

        return view('admin.users.index');
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent viewing admin users
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Cannot view admin user details.'], 403);
            }
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number ?? 'N/A',
                'address' => $user->address ?? 'N/A',
                'role' => $user->role,
                'status' => $user->status,
                'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'Not Verified',
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent toggling status for admin users
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Cannot modify admin user status.'], 403);
            }
            
            if ($user->status === 'active') {
                $user->status = 'inactive';
                $message = 'User status changed to inactive successfully.';
            } else {
                $user->status = 'active';
                $message = 'User status changed to active successfully.';
            }
            
            $user->save();

            return response()->json(['success' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting admin users
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Cannot delete admin user.'], 403);
            }
            $user->delete();

            return response()->json(['success' => 'User deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}

