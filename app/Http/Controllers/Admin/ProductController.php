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
    public function index(Request $request)
    {
        $categories = Category::where('status', 'active')->orderBy('id', 'desc')->get();
        $stockFilter = $request->get('stock');
        if (!in_array($stockFilter, ['out_of_stock'], true)) {
            $stockFilter = null;
        }

        return view('admin.products.index', compact('categories', 'stockFilter'));
    }

    // Fetch all products
    public function getall(Request $request)
    {
        $query = Product::with('category', 'colors')->orderBy('id', 'desc');

        if ($request->get('stock') === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->get();
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
            'description' => 'nullable|string|max:1000',

            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif',

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

        $data = $request->only(['name','category_id','price','stock','description']);

        $images = ['image','image2','image3','image4'];

        foreach ($images as $img) {

            if ($request->hasFile($img)) {

                $imageName = time().'_'.$request->file($img)->getClientOriginalName();
                $request->file($img)->move(public_path('uploads/product'), $imageName);

                $data[$img] = url('uploads/product/'.$imageName);

            } else {

                $data[$img] = null;
            }
        }

        $product = Product::create($data);

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

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable|string|max:1000',

            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif',

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

        $data = $request->only(['name','category_id','price','stock','description']);

        $images = ['image','image2','image3','image4'];

        foreach ($images as $img) {

            if ($request->hasFile($img)) {

                if ($product->$img) {

                    $oldImage = str_replace(url('/').'/', '', $product->$img);

                    if (file_exists(public_path($oldImage))) {

                        unlink(public_path($oldImage));
                    }
                }

                $imageName = time().'_'.$request->file($img)->getClientOriginalName();
                $request->file($img)->move(public_path('uploads/product'), $imageName);

                $data[$img] = url('uploads/product/'.$imageName);
            }
        }

        $product->update($data);

        if ($request->has('colors')) {

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
