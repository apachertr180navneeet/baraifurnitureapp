<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class BannerController extends Controller
{
    /**
     * Show banner index page.
     */
    public function index(Request $request)
    {
        return view('admin.banners.index');
    }

    /**
     * Fetch all banners.
     */
    public function getall(Request $request)
    {
        $banners = Banner::orderBy('id', 'desc')->get();

        return response()->json(['data' => $banners], 200);
    }

    /**
     * Update status (active/inactive) of a banner.
     */
    public function status(Request $request)
    {
        try {
            $banner = Banner::find($request->id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found',
                ], 404);
            }

            $banner->status = $request->status;
            $banner->save();

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
     * Delete a banner by ID (soft delete + image delete).
     */
    public function destroy($id)
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found',
                ], 404);
            }

            // Delete image from folder if exists
            if ($banner->image) {
                $oldImagePath = public_path(parse_url($banner->image, PHP_URL_PATH));
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new banner.
     */
    public function store(Request $request)
    {
        $rules = [
            'title'  => 'required|string|min:3|max:100',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banner'), $fileName);

            $imagePath = url('uploads/banner/' . $fileName);
        }

        $banner = Banner::create([
            'title'  => $request->title,
            'image'  => $imagePath,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner saved successfully!',
            'data'    => $banner,
        ], 201);
    }

    /**
     * Fetch single banner by ID.
     */
    public function get($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found',
            ], 404);
        }

        return response()->json($banner, 200);
    }

    /**
     * Update banner data.
     */
    public function update(Request $request)
    {
        $rules = [
            'id'     => 'required|exists:banners,id',
            'title'  => 'required|string|min:3|max:100',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $banner = Banner::find($request->id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found',
            ], 404);
        }

        $imagePath = $banner->image;
        if ($request->hasFile('image')) {
            // delete old image
            if ($banner->image) {
                $oldImagePath = public_path(parse_url($banner->image, PHP_URL_PATH));
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banner'), $fileName);

            $imagePath = url('uploads/banner/' . $fileName);
        }

        $banner->update([
            'title'  => $request->title,
            'image'  => $imagePath,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully!',
            'data'    => $banner,
        ], 200);
    }
}
