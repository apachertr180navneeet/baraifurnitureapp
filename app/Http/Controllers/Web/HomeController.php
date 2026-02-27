<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('web.home.index');
    }

    public function privacy()
    {
        return view('web.home.privacy_policy');
    }


    public function deleteUser()
    {
        return view('web.home.delete_user');
    }


    public function deleteByMobile(Request $request)
    {
        try {

            // ✅ Validate mobile number
            $request->validate([
                'mobile' => 'required|digits_between:10,15'
            ]);

            // ✅ Find user (company-wise restriction for ERP)
            $user = User::where('phone', $request->mobile)
                        ->first();

            // ❌ If not found
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found with this mobile number.'
                ], 404);
            }

            // ❌ Prevent deleting yourself (recommended)
            if ($user->id == auth()->id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            // ✅ Delete user (Soft delete if enabled)
            $user->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage() // remove in production
            ], 500);
        }
    }
}
