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

            $query = GenarateQuotation::with(['customer']);

            // Date filter
            if ($request->start_date && $request->end_date) {

                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);

            }

            // If only start date
            if ($request->start_date && !$request->end_date) {

                $query->whereDate('created_at', '>=', $request->start_date);

            }

            // If only end date
            if (!$request->start_date && $request->end_date) {

                $query->whereDate('created_at', '<=', $request->end_date);

            }

            $orders = $query->orderBy('id', 'desc')->get();

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

            return response()->json([
                'data' => $orders
            ], 200);

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
            $order = GenarateQuotation::find($request->id);

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
