<?php

namespace App\Http\Controllers\v1;

use App\Models\Photo;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Validator, DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PhotoFromPhotographer;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //DISPLAY ALL PHOTOS
    public function index()
    {
        $photo = Photo::with('product')->paginate(10);

        if (!$photo) {
            return response()->json([
                "error" => true,
                "message" => "Photos not found",
                "data" => null
            ]);
        }
        return response()->json([
            "error" => false,
            "message" => null,
            "data" => $photo
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //CREATE NEW PHOTO
    public function store(Request $request)
    {
        $credentials = $request->all();

        $rules = [
            ['product_photo' => 'required'],
            ['product_photo' => 'image|mimes:jpg,png'],
            ['product_id' => 'required'],
        ];

        $validatorEmployee = Validator::make($credentials, $rules[0]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Photo attachment is required.',
                'data' => null
            ], 422);
        }

        $validatorEmployee = Validator::make($credentials, $rules[1]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> $validatorEmployee->messages()->all(),
                'data' => null
            ], 422);
        }

        $validatorEmployee = Validator::make($credentials, $rules[2]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Product id is required.',
                'data' => null
            ], 422);
        }

        if(Auth::user()->hasRole('photographer')){
            DB::beginTransaction();

            $fileExt = $request->product_photo->getClientOriginalExtension();
            $name =  Str::upper('photo1').'_'.date("Y-m-d").'_'.time().'.'.$fileExt;
            $attachmentName = config('app.url').'/'.'images/'.$name;

            $storeFile = $request->product_photo->move(public_path('images'), $attachmentName);

            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json([
                    "error" => true,
                    "message" => "Product not found",
                    "data" => null
                ], 422);
            }

            $request = ProductRequest::where('product_id', $product->id)->where('photo_taken', 0)->first();

            if (!$request) {
                return response()->json([
                    "error" => true,
                    "message" => "Request not found",
                    "data" => null
                ], 422);
            }

            $user_id = $product->user_id;

            $user = User::where('id', $user_id)->first();

            $photo = Photo::create([
                'product_photo' => $attachmentName,
                'product_id' => $product->id,
            ]);
            if ($photo) {
                Notification::send($user, new PhotoFromPhotographer());
                DB::commit();
                return response()->json([
                    "error" => false,
                    "message" => 'Photo created successfully',
                    "data" => $photo
                ], 201);
            }
            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "Photo could not be created",
                "data" => null
            ]);
        }else{
            return response()->json([
                "error" => true,
                "message" => "You are not a Photographer",
                "data" => null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    //Product Owners approves a photo
    public function approve(Request $request)
    {
        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();

            $product = Product::where('user_id', Auth::id())->first();
            if (!$product) {
                return response()->json([
                    "error" => true,
                    "message" => "Product not found for this user",
                    "data" => null
                ], 422);
            }

            $request = ProductRequest::where('product_id', $product->id)->where('photo_taken', 0)->first();
            if (!$request) {
                return response()->json([
                    "error" => true,
                    "message" => "Request not found for this user",
                    "data" => null
                ], 422);
            }

            $photo = Photo::where('product_id', $product->id)->where('is_approved', 0)->first();
            if (!$photo) {
                return response()->json([
                    "error" => true,
                    "message" => "Photo not found for this user",
                    "data" => null
                ], 422);
            }

            $photo->update([
                'photo_taken' => 1,
            ]);

            return response()->json([
                "error" => false,
                "message" => 'Photo Approved',
                "data" => $photo
            ]);

            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "Photo could not be approved",
                "data" => null
            ], 422);
        }else{
            return response()->json([
                "error" => true,
                "message" => "You are not a Product Owner",
                "data" => null
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    //Product Owners rejects a photo
    public function reject(Request $request)
    {
        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();
            $product = Product::where('user_id', Auth::id())->first();
            if (!$product) {
                return response()->json([
                    "error" => true,
                    "message" => "Product not found for this user",
                    "data" => null
                ], 422);
            }

            $request = ProductRequest::where('product_id', $product->id)->where('photo_taken', 0)->first();
            if (!$request) {
                return response()->json([
                    "error" => true,
                    "message" => "Request not found for this user",
                    "data" => null
                ], 422);
            }

            $photo = Photo::where('product_id', $product->id)->where('is_approved', 0)->first();
            if (!$photo) {
                return response()->json([
                    "error" => true,
                    "message" => "Photo not found for this user",
                    "data" => null
                ], 422);
            }

            $photo->update([
                'photo_taken' => 0,
            ]);

            return response()->json([
                "error" => false,
                "message" => 'Photo Rejected',
                "data" => null
            ]);

            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "Photo could not be rejected",
                "data" => null
            ], 422);
        }else{
            return response()->json([
                "error" => true,
                "message" => "You are not a Product Owner",
                "data" => null
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Photo $photo)
    {
        //
    }
}
