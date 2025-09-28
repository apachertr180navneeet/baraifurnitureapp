<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Exception;

class CategoryController extends Controller
{
    // Show categories index page
    public function index()
    {
        return view('admin.category.index');
    }

    // Fetch all categories
    public function getall(Request $request)
    {
        $categories = Category::orderBy('id', 'desc')->get();
        return response()->json(['data' => $categories], 200);
    }

    // Store a new category (without status)
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:categories,name',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
            // status will use default from migration (1)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!',
            'data'    => $category,
        ], 201);
    }

    // Fetch single category by ID
    public function get($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json($category, 200);
    }

    // Update category (without status)
    public function update(Request $request)
    {
        $rules = [
            'id'   => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories,name,' . $request->id,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category = Category::find($request->id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
            'data'    => $category,
        ], 200);
    }

    // Update status separately
    public function status(Request $request)
    {
        try {
            $category = Category::find($request->id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            $category->status = $request->status;
            $category->save();

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

    // Soft delete category
    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
