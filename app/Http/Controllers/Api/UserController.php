<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use App\Models\Cart;
use App\Models\Product;
use App\Models\GenarateQuotation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use App\Models\SplashScreen;


use PDF; // barryvdh/laravel-dompdf



class UserController extends Controller
{
    
    public function addToCart(Request $request)
    {
        $user = auth()->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $item_id = $request->item_id;
        $quantity = $request->quantity;

        // Get item details to calculate amount
        $item = Product::find($item_id);
        if (!$item) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 200);
        }

        $amount = $quantity * $item->price;
        // Check if the item is already in the cart
        $cartItem = Cart::where('user_id', $user->id)
                        ->where('item_id', $item_id)
                        ->first();

        if ($cartItem) {
            // Update quantity and amount
            $cartItem->quantity += $quantity;
            $cartItem->amount += $amount; // total amount
            $cartItem->save();
        } else {
            // Add new item to cart
            Cart::create([
                'user_id' => $user->id,
                'item_id' => $item_id,
                'quantity' => $quantity,
                'amount' => $amount,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Item added to cart successfully',
        ], 200);
    }

    public function getCart()
    {
        $user = auth()->user();

        $cart = Cart::with('item')->where('user_id', $user->id)->get()->map(function($cartItem) {
            return [
                'id' => $cartItem->id ?? "",
                'user_id' => $cartItem->user_id ?? "",
                'item_id' => $cartItem->item_id ?? "",
                'quantity' => $cartItem->quantity ?? "",
                'amount' => $cartItem->amount ?? 0,
                'status' => $cartItem->status ?? "",
                'created_at' => $cartItem->created_at ?? "",
                'updated_at' => $cartItem->updated_at ?? "",
                // Add item fields directly
                'item_name' => $cartItem->item->name ?? "",
                'item_code' => $cartItem->item->code ?? "",
                'item_category_id' => $cartItem->item->category_id ?? "",
                'item_qty' => $cartItem->item->qty ?? "",
                'item_image' => $cartItem->item->image ?? "",
                'item_price' => $cartItem->item->price ?? 0,
            ];
        });

        // Calculate total amount
        $totalAmount = $cart->sum('amount');

        return response()->json([
            'status' => true,
            'data' => $cart,
            'total_amount' => $totalAmount,
        ], 200);
    }


    public function removeCart(Request $request)
    {
        $user = auth()->user();
        $id = $request->cart_id; // get cart item ID from request

        // Check if the cart item exists for this user
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found.',
            ], 200);
        }

        // Force delete the cart item
        $cartItem->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Cart item removed.',
        ], 200);
    }

    public function genarateQuotation(Request $request)
    {
        $user = auth()->user();
        $cartItems = Cart::with('item')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty.'
            ]);
        }

        $quotationRows = [];
        $totalAmount = 0;

        foreach ($cartItems as $cart) {
            $amount = $cart->quantity * $cart->item->price;
            $totalAmount += $amount;

            $quotationRows[] = GenarateQuotation::create([
                'user_id' => $user->id,
                'item_id' => $cart->item_id,
                'quantity' => $cart->quantity,
                'status' => 1,
                'amount' => $amount,
            ]);
        }

        // Generate PDF
        $pdf = PDF::loadView('quotations.pdf', [
            'user' => $user,
            'quotationRows' => $quotationRows,
            'totalAmount' => $totalAmount,
        ]);

        $fileName = 'quotation_'.$user->id.'_'.time().'.pdf';
        $uploadPath = public_path('uploads/quotations/');

        // Make directory if not exists
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fileFullPath = $uploadPath . $fileName;
        $pdf->save($fileFullPath);

        // Save PDF URL in each row
        $pdfUrl = url('uploads/quotations/' . $fileName);
        foreach ($quotationRows as $row) {
            $row->pdf_url = $pdfUrl;
            $row->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Quotation generated successfully.',
            'pdf_url' => $pdfUrl,
        ]);
    }


}
