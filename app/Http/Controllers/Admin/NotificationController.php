<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class NotificationController extends Controller
{
    /**
     * Show customer index page.
     */
    public function index(Request $request)
    {
        return view('admin.notifications.index');
    }

    /**
     * Fetch all customers (role = user).
     */
    public function getall(Request $request)
    {
        $customers = Notification::orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $customers], 200);
    }

    /**
     * Update status (active/inactive) of a customer.
     */
    public function status(Request $request)
    {
        try {
            $notification = Notification::find($request->id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->status = $request->status;
            $notification->save();

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
            $customer = Notification::find($id);

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
     * Store a new notification.
     */
    public function store(Request $request)
    {
        $rules = [
            'date'        => 'required|date',
            'title'       => 'required|string|min:3|max:100',
            'description' => 'required|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        $notification = Notification::create([
            'date'        => $request->date,
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification saved successfully!',
            'data'    => $notification,
        ], 201); // Created
    }

    /**
     * Fetch single notification by ID.
     */
    public function get($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json($notification, 200);
    }

    /**
     * Update notification data.
     */
    public function update(Request $request)
    {
        $rules = [
            'id'          => 'required|exists:notifications,id',
            'date'        => 'required|date',
            'title'       => 'required|string|min:3|max:100',
            'description' => 'required|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $notification = Notification::find($request->id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->update($request->only(['date', 'title', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully!',
            'data'    => $notification,
        ], 200);
    }
}
