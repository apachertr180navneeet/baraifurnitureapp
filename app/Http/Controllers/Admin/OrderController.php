<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\GenarateQuotation;
use App\Models\Product;
use Exception;

class OrderController extends Controller
{
    /**
     * Show orders page
     */
    public function index()
    {
        return view('admin.orders.index');
    }

    /**
     * Fetch all orders
     */
    public function getall(Request $request)
    {
        try {
            $orders = GenarateQuotation::with(['customer'])->orderBy('id', 'desc')->get();

            $orders->transform(function ($order) {
                $itemIds = collect(explode(',', (string) $order->item_id))
                    ->map(function ($id) {
                        return (int) trim($id);
                    })
                    ->filter()
                    ->values();

                $productNames = Product::whereIn('id', $itemIds->all())
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->implode(', ');

                $order->product_names = $productNames;

                return $order;
            });

            return response()->json(['data' => $orders], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function status(Request $request)
    {
        try {
            $order = Order::find($request->id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $request->validate([
                'status' => 'required|in:pending,completed,cancelled',
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
