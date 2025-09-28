<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProductController extends Controller
{
    // Show products index page
    public function index()
    {
        $categories = Category::where('status', 'active')->orderBy('id', 'desc')->get();
        return view('admin.products.index', compact('categories'));
    }

    // Fetch all products
    public function getall()
    {
        $products = Product::with('category', 'colors')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $products], 200);
    }

    // Store new product
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'required_with:colors|string|max:255',
            'colors.*.qty' => 'required_with:colors|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        // Add colors
        if ($request->has('colors')) {
            foreach ($request->colors as $color) {
                $product->colors()->create($color);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'data' => $product->load('colors')
        ], 201);
    }

    // Fetch single product
    public function get($id)
    {
        $product = Product::with('colors', 'category')->find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        return response()->json($product, 200);
    }

    // Update product
    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'required_with:colors|string|max:255',
            'colors.*.qty' => 'required_with:colors|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Update image
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update($request->only(['name', 'category_id', 'price', 'stock', 'image']));

        // Update colors
        if ($request->has('colors')) {
            // Delete existing colors
            $product->colors()->delete();
            foreach ($request->colors as $color) {
                $product->colors()->create($color);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'data' => $product->load('colors')
        ], 200);
    }

    // Soft delete product
    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product status
     */
    public function status(Request $request)
    {
        try {
            $product = Product::find($request->id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $product->status = $request->status; // 'active' or 'inactive'
            $product->save();

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
}
