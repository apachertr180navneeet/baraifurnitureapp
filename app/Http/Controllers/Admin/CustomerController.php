<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class CustomerController extends Controller
{
    /**
     * Show customer index page.
     */
    public function index(Request $request)
    {
        return view('admin.customer.index');
    }

    /**
     * Fetch all customers (role = user).
     */
    public function getall(Request $request)
    {
        $customers = User::where('role', 'user')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $customers], 200);
    }

    /**
     * Update status (active/inactive) of a customer.
     */
    public function status(Request $request)
    {
        try {
            $customer = User::find($request->userId);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            $customer->status = $request->status;
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a customer by ID.
     */
    public function destroy($id)
    {
        try {
            $customer = User::find($id);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new customer.
     */
    public function store(Request $request)
    {
        $rules = [
            'full_name'    => 'required|string|min:3|max:50',
            'email'   => 'required|email|unique:users,email',
            'phone'   => 'required|digits_between:10,15|unique:users,phone',
            'city' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        $customer = User::create([
            'full_name'    => $request->full_name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'city' => $request->city,
            'role'    => 'user',
            'status'  => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer saved successfully!',
            'data'    => $customer,
        ], 201); // Created
    }

    /**
     * Fetch single customer by ID.
     */
    public function get($id)
    {
        $customer = User::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        return response()->json($customer, 200);
    }

    /**
     * Update customer data.
     */
    public function update(Request $request)
    {
        $rules = [
            'id'      => 'required|exists:users,id',
            'full_name'    => 'required|string|min:3|max:50',
            'email'   => 'required|email|unique:users,email,' . $request->id,
            'phone'   => 'required|digits_between:10,15|unique:users,phone,' . $request->id,
            'city' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $customer = User::find($request->id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $customer->update($request->only(['full_name', 'email', 'phone', 'address']));

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully!',
            'data'    => $customer,
        ], 200);
    }
}
