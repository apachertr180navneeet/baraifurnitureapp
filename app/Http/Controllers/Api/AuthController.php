<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use App\Mail\UserRegisterVerifyMail;
use App\Models\EmailOtp;
use App\Models\PhoneOtp;
use App\Models\AppUser;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use App\Models\SplashScreen;



class AuthController extends Controller
{
    
    public function splashScreens(){
        $base_url = asset('/');
        $splash_screens = SplashScreen::select('type','heading','content','image')->get();
        foreach ($splash_screens as $key => $screen) {
            if($screen['image']){
                $screen['image'] = $base_url.$screen['image'];
            }
        }
        return response()->json([
            'status' => true,
            'data' => $splash_screens,
        ],200);

    }

    public function sendPhoneOtp(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => 'required|max:5'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ], 200);
        }

        // For testing: fixed OTP (change to rand(1000,9999) in production)
        $code = rand(1000,9999);
        //$code = '1234';

        $date = date('Y-m-d H:i:s');
        $currentDate = strtotime($date);
        $futureDate = $currentDate + (60 * 120);

        $phone_user = PhoneOtp::where('country_code', $data['country_code'])
                            ->where('phone', $data['phone'])
                            ->first();

        if (!$phone_user) {
            $phone_user = new PhoneOtp();
        }

        $phone_user->phone = $data['phone'];
        $phone_user->country_code = $data['country_code'];
        $phone_user->otp = $code;
        $phone_user->otp_expire_time = $futureDate;
        $phone_user->save();

        return response()->json([
            'status' => true,
            'message' => 'A one-time password has been sent to your phone, please check.',
            'otp' => $code,  // ✅ OTP also returned in response
        ], 200);
    }

    public function verifyPhoneOtp(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => 'required|digits_between:4,13',
            'country_code' => "required|max:5",
            'otp' => "required|max:4",
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }
        
        $phone_user = PhoneOtp::where('country_code',$data['country_code'])
                            ->where('phone',$data['phone'])
                            ->first();

        if(!$phone_user){
            return response()->json([
                'status' => false,
                'message'=>'Invalid phone number. Please check and try again'
            ],200);
        }

        $currentTime = strtotime(now());

        if($phone_user->otp != $data['otp']){
            return response()->json([
                'status' => false,
                'message' =>  'Invalid verification code. Please try again',
            ],200);
        }

        if($currentTime > $phone_user->otp_expire_time){
            return response()->json([
                'status' => false,
                'message' =>  'Verification code is expired.',
            ],200);
        }

        // OTP verified → delete it
        PhoneOtp::where('country_code',$data['country_code'])
                ->where('phone',$data['phone'])
                ->delete();

        // Find user
        $user = User::where('phone',$data['phone'])
                    ->where('country_code',$data['country_code'])
                    ->where('role','user')
                    ->first();

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'Phone number not exists',
            ],200);
        }

        if($user->status == 'inactive'){
            return response()->json([
                'status' => false,
                'message' => 'Your account is not activated yet.',
            ],200);
        }

        // Prepare JWT login
        $credentials = [
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'password' => $user->full_name, // same logic you used in login()
        ];

        try {
            if(!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message'=>'Something went wrong. Please try again'
                ],200);
            }

            // Save device info
            $user->save();

            return response()->json([
                'status' => true,
                'message'=>'Verified & logged in successfully.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail($user->id),
            ],200);

        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }
    }

    public function register(Request $request) 
    {
        // Validate required fields
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string',
            'email'        => 'required|email|unique:users',
            'phone'        => 'required|numeric|digits_between:4,12|unique:users',
            'address'      => 'required|string',
            'country_code' => 'required|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        try {
            // Delete old record with same phone (if exists)
            AppUser::where('phone', $request->phone)
                ->where('country_code', $request->country_code)
                ->delete();

            // Create new user
            $app_user = new AppUser();
            $app_user->full_name    = $request->full_name;
            $app_user->slug         = Helper::slug('users', $request->full_name);
            $app_user->email        = $request->email;
            $app_user->phone        = $request->phone;
            $app_user->country_code = $request->country_code;
            $app_user->address      = $request->address;
            $app_user->city         = $request->city;
            $app_user->password = $request->full_name;

            $app_user->save();

            // ✅ Generate and save OTP
            $otp = rand(1000, 9999);
            $date = now();
            $expireTime = strtotime($date) + (60 * 120); // 2 hours expiry

            $phone_user = PhoneOtp::where('country_code', $request->country_code)
                ->where('phone', $request->phone)
                ->first();

            if (!$phone_user) {
                $phone_user = new PhoneOtp();
            }

            $phone_user->phone            = $request->phone;
            $phone_user->country_code     = $request->country_code;
            $phone_user->otp              = $otp;
            $phone_user->otp_expire_time  = $expireTime;
            $phone_user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Otp is sent on your phone! Please verify otp to complete your registration',
                'otp'     => $otp  // 👈 sending OTP in response
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }


    public function verifyRegister(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'phone' => "required|numeric|exists:app_users,phone|unique:users,phone",
            'otp' => "required|max:4",
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' =>  $validator->errors()->first(),
            ],200);
        }

        
        $date = date('Y-m-d H:i:s');
        $currentTime = strtotime($date);
        $phone_user = PhoneOtp::where('phone',$data['phone'])->where('otp',$data['otp'])->first();
        $app_user = AppUser::where('phone',$data['phone'])->first();
        
        if(!$phone_user){
            return response()->json([
                'status' => false,
                'message'=>'Please enter valid otp.'
            ],200);
            
        }
        if($currentTime > $phone_user->otp_expire_time){
            return response()->json([
                'status' => true,
                'message' =>  'Otp time is expired.',
            ],200);
        }

        try{
            DB::beginTransaction();
            $user = new User();
            $user->first_name = $app_user->first_name;
            $user->last_name = $app_user->last_name;
            $user->full_name = $app_user->full_name;
            $user->email = $app_user->email;
            $user->slug = $app_user->slug;
            $user->phone = $app_user->phone;
            $user->password = bcrypt($app_user->full_name);
            $user->address = $app_user->address;
            $user->area = $app_user->area ?? '';
            $user->city = $app_user->city ?? '';
            $user->state = $app_user->state ?? '';
            $user->country = $app_user->country ?? '';
            $user->country_code = $app_user->country_code;
            $user->zipcode = $app_user->zipcode ?? '';
            $user->latitude = $app_user->latitude ?? '';
            $user->longitude = $app_user->longitude ?? '';
            $user->device_type = $app_user->device_type ?? '';
            $user->device_token = $app_user->device_token ?? '';
            $user->bio = $app_user->bio ?? '';
            $user->phone_verified_at = $date;
            $user->avatar = $app_user->avatar;
            $user->role = 'user';
            $user->status = 'active';
            $user->save();
            DB::commit();
            

            // Mail::to($user->email)->send(new UserRegisterVerifyMail($user));
            //============ Make User Login ==========//
            $input['phone'] = $app_user->phone;
            // $input['country_code'] = $app_user->country_code;
            $input['password'] = $app_user->full_name;
            $token = JWTAuth::attempt($input);
            $app_user->delete();
            
            return response()->json([
                'status' => true,
                'message'=>'Account created successfully!',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $this->getUserDetail($user->id),
            ],200);
            
        }catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],200);
        }

    }

    protected function createNewToken($token){
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'message'=>'Token refresh successfully.'
        ],200);
    }

    public function getUser() 
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 200);
            }

            // Get user details
            $userData = $this->getUserDetail($user->id);

            // Specific fields replace null → ""
            foreach (['full_name', 'email', 'phone', 'address'] as $field) {
                if (isset($userData[$field]) && $userData[$field] === null) {
                    $userData[$field] = "";
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'User found successfully.',
                'user' => $userData,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Recursive function to replace null with ""
     */
    private function replaceNullWithEmptyString($data)
    {
        if (is_array($data)) {
            return array_map(function ($value) {
                return $this->replaceNullWithEmptyString($value);
            }, $data);
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->replaceNullWithEmptyString($value);
            }
            return $data;
        } else {
            return $data === null ? "" : $data;
        }
    }
    
    public function updateProfile(Request $request)
    {
        $id = auth()->user()->id;

        $validator = Validator::make($request->all(), [
            'full_name'     => 'sometimes|string',
            'email'         => 'sometimes|email|unique:users,email,' . $id,
            'phone'         => 'sometimes|numeric|digits_between:4,12|unique:users,phone,' . $id,
            'address'       => 'sometimes|string',
            'country_code'  => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        try {
            $user = User::find($id);

            if ($request->filled('full_name')) {
                $user->full_name = $request->full_name;
                $user->password = bcrypt($request->full_name);
            }

            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            if ($request->filled('phone')) {
                $user->phone = $request->phone;
            }

            if ($request->filled('address')) {
                $user->address = $request->address;
            }

            if ($request->filled('country_code')) {
                $user->country_code = $request->country_code;
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully!',
                'user' => $this->getUserDetail($user->id),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    
    public function getUserDetail($user_id){
        $user = User::where('id',$user_id)->first();
        return $user;
    }

    public function logout() {
        JWTAuth::parseToken()->invalidate(true);
        return response()->json(array(
            'status' => true,
            'message' => 'User successfully signed out.'
        ),200);
    }

    public function deleteAccount()
    {
        try{
            DB::beginTransaction();
            $user = Auth::user();
            $user->email = uniqid().'_delete_'.$user->email;
            $user->phone = uniqid().'_delete_'.$user->phone;
            $user->status = 'inactive';
            $user->save();
            DB::commit();
            Auth::logout();
            return response()->json(array(
                'status' => true,
                'message' => 'Account deleted successfully.'
            ),200);
            
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json(array(
                'status' => false,
                'message' => $e->getMessage()
            ),200);
        }   


    }

    public function dashboard(){
        $base_url = asset('/');

        // Get active banners
        $banners = Banner::where('status', 'active')->get();
        foreach ($banners as $key => $banner) {
            if ($banner->image) {
                $banner->image = $banner->image; // You can append $base_url if needed
            }
        }

        // Get active categories
        $categories = Category::where('status', 'active')->get();

        // Get active items with category and colors
        $items = Product::with('category', 'colors')->where('status', 'active')->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category ? $item->category->name : null,
                'price' => $item->price,
                'stock' => $item->stock,
                'image' => $item->image,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
                'colors' => $item->colors->map(function($color) {
                    return [
                        'color_name' => $color->color_name,
                        'qty' => $color->qty,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'sliders' => $banners,
                'categories' => $categories,
                'items' => $items,
            ],
        ], 200);
    }

    public function categories()
    {
        $categories = Category::where('status','active')->get()->map(function ($category) {
            return collect($category)->map(function ($value) {
                return $value ?? "";
            });
        });

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ], 200);
    }

    public function items(Request $request)
    {
        $query = Product::with(['category:id,name', 'colors'])
                        ->where('status', 'active');

        // Filter by category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Pagination
        $products = $query->paginate(10);

        // Transform each product
        $products->getCollection()->transform(function ($item) {
            $data = [
                'id'            => $item->id,
                'name'          => $item->name,
                'code'          => $item->code ?? "",
                'category_id'   => $item->category_id,
                'category_name' => $item->category->name ?? "",
                'price'         => $item->price,
                'stock'         => $item->stock,
                'image'         => $item->image,
                'status'        => $item->status,
                'created_at'    => $item->created_at,
                'updated_at'    => $item->updated_at,
                'deleted_at'    => $item->deleted_at,
                'colors'        => $item->colors->map(function($color) {
                    return [
                        'color_name' => $color->color_name ?? "",
                        'qty'        => $color->qty ?? 0,
                    ];
                }),
            ];

            // Replace null with empty string
            return collect($data)->map(function ($value) {
                return $value ?? "";
            });
        });

        return response()->json([
            'status' => true,
            'data'   => $products,
        ], 200);
    }


    public function itemDetails($id)
    {
        $product = Product::with(['category:id,name', 'colors'])
                        ->where('id', $id)
                        ->where('status', 'active')
                        ->first();

        if (!$product) {
            return response()->json([
                'status'  => false,
                'message' => 'Product not found',
            ], 200);
        }

        $productData = [
            'id'            => $product->id,
            'name'          => $product->name,
            'code'          => $product->code ?? "",
            'category_id'   => $product->category_id,
            'category_name' => $product->category->name ?? "",
            'price'         => $product->price,
            'stock'         => $product->stock,
            'description'   => $product->description ?? "",
            'image'         => $product->image,
            'status'        => $product->status,
            'created_at'    => $product->created_at,
            'updated_at'    => $product->updated_at,
            'deleted_at'    => $product->deleted_at,
            'colors'        => $product->colors->map(function($color) {
                return [
                    'color_name' => $color->color_name ?? "",
                    'qty'        => $color->qty ?? 0,
                ];
            }),
        ];

        // Convert null values to empty strings
        $productData = collect($productData)->map(function ($value) {
            return $value ?? "";
        });

        return response()->json([
            'status' => true,
            'data'   => $productData,
        ], 200);
    }

}
