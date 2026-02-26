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
use App\Models\CustomizeOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use App\Models\SplashScreen;

use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;






class UserController extends Controller
{
    private function nullToBlank($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->nullToBlank($value);
            } elseif (is_null($value)) {
                $array[$key] = "";
            }
        }
        return $array;
    }
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
            $cartItem->quantity = $quantity;
            $cartItem->amount = $amount; // total amount
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

        $itemIds = [];
        $quantities = [];
        $quotationDetails = [];
        $totalAmount = 0;

        foreach ($cartItems as $cart) {
            $amount = $cart->quantity * $cart->item->price;
            $totalAmount += $amount;
            $itemIds[] = $cart->item_id;
            $quantities[] = $cart->quantity;

            $quotationDetails[] = [
                'quotation_id' => null,
                'item_id' => (string) $cart->item_id,
                'item_name' => $cart->item->name ?? '',
                'item_code' => $cart->item->code ?? '',
                'item_image' => $cart->item->image ?? '',
                'item_price' => $cart->item->price ?? 0,
                'quantity' => $cart->quantity,
                'amount' => $amount,
                'pdf_url' => '',
            ];
        }

        $quotation = GenarateQuotation::create([
            'user_id' => $user->id,
            'item_id' => implode(',', $itemIds),
            'quantity' => implode(',', $quantities),
            'status' => 1,
            'amount' => $totalAmount,
        ]);

        // Generate PDF
        $pdf = PDF::loadView('quotations.pdf', [
            'user' => $user,
            'quotationItems' => $quotationDetails,
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

        // Save PDF URL in quotation row
        $pdfUrl = url('uploads/quotations/' . $fileName);
        $quotation->pdf_url = $pdfUrl;
        $quotation->save();

        foreach ($quotationDetails as &$detail) {
            $detail['quotation_id'] = $quotation->id;
            $detail['pdf_url'] = $pdfUrl;
        }
        unset($detail);

        // Clear all cart items for the user
        Cart::where('user_id', $user->id)->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Quotation generated successfully.',
            'pdf_url' => $pdfUrl,
            'total_amount' => $totalAmount,
            'user_detail' => [
                'name' => $user->full_name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
            ],
            'quotation_details' => $quotationDetails,
        ]);
    }

    public function genarateQuotationDetail($id)
    {
        $authUser = auth()->user();

        $quotation = GenarateQuotation::with([
                'user:id,full_name,email',
            ])
            ->where('user_id', $authUser->id)
            ->where('id', $id)
            ->first();

        if (!$quotation) {
            return response()->json([
                'status' => false,
                'message' => 'Quotation not found.'
            ], 404);
        }

        $itemIds = collect(explode(',', (string) $quotation->item_id))
            ->map(function ($id) {
                return (int) trim($id);
            })
            ->filter()
            ->values();

        $quantities = collect(explode(',', (string) $quotation->quantity))
            ->map(function ($qty) {
                return (int) trim($qty);
            })
            ->values();

        $products = Product::whereIn('id', $itemIds->all())
            ->get()
            ->keyBy('id');

        $items = [];
        $totalAmount = 0;

        foreach ($itemIds as $index => $itemId) {
            $product = $products->get($itemId);
            $qty = $quantities->get($index, 1);
            if ($qty < 1) {
                $qty = 1;
            }

            $price = (float) ($product->price ?? 0);
            $amount = $qty * $price;
            $totalAmount += $amount;

            $items[] = [
                'item_id' => $itemId,
                'quantity' => $qty,
                'amount' => $amount,
                'item' => $product,
            ];
        }

        $data = [
            'quotation_id' => $quotation->id,
            'item_ids' => (string) $quotation->item_id,
            'quantities' => (string) $quotation->quantity,
            'pdf_url' => $quotation->pdf_url,
            'user' => $quotation->user,
            'total_amount' => $totalAmount,
            'items' => $items,
        ];

        $data = $this->nullToBlank($data);

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }

    public function customizeOrders(Request $request)
    {
        $user = auth()->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'date' => 'required|string',
            'name' => 'required|string',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $customizeOrder = new CustomizeOrder();
        $customizeOrder->customerId = $user->id;
        $customizeOrder->coustomername = $request->name;
        $customizeOrder->date = $request->date;
        $customizeOrder->remark = $request->remark;

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

            // Destination path
            $destinationPath = public_path('uploads/customize_orders');

            // Create directory if not exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move file to public/uploads/customize_orders
            $image->move($destinationPath, $filename);

            // Save URL in database
            $customizeOrder->image = asset('uploads/customize_orders/' . $filename);
        }

        $customizeOrder->save();

        return response()->json([
            'status' => true,
            'message' => 'Customize order created successfully.',
            'data' => $customizeOrder
        ], 200);
    }


}
