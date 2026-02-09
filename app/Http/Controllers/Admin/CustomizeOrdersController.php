<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomizeOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class CustomizeordersController extends Controller
{
    /**
     * Show customize orders page
     */
    public function index()
    {
        return view('admin.customize_orders.index');
    }

    /**
     * Fetch all customize orders
     */
    public function getall(Request $request)
    {
        try {
            $orders = CustomizeOrder::with(['customer'])->orderBy('id', 'desc')->get();
            return response()->json(['data' => $orders], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch single order by ID
     */
    public function get($id)
    {
        $order = CustomizeOrder::with(['customer'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * Update order data
     */
    public function update(Request $request)
    {
        $rules = [
            'id'         => 'required|exists:customize_orders,id',
            'remark'     => 'nullable|string',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $order = CustomizeOrder::find($request->id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $data = $request->only(['date', 'orderId', 'customerId', 'remark', 'status']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($order->image && Storage::exists('public/customize_orders/' . basename($order->image))) {
                Storage::delete('public/customize_orders/' . basename($order->image));
            }

            $image = $request->file('image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/customize_orders', $filename);

            // Store full URL in database
            $data['image'] = asset('storage/customize_orders/' . $filename);
        }

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'data'    => $order,
        ], 200);
    }

    /**
     * Update order status only
     */
    public function status(Request $request)
    {
        try {
            $order = CustomizeOrder::find($request->id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $request->validate([
                'status' => 'required|in:pending,in_progress,completed,cancelled',
            ]);

            $order->status = $request->status;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
