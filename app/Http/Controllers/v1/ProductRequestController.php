<?php

namespace App\Http\Controllers\v1;

use App\Models\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Validator, DB;

class ProductRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //DISPLAY ALL PRODUCT REQUESTS
    public function index()
    {
        if(Auth::user()->hasRole('photographer')){
            $requests = ProductRequest::with('product')->where('photo_taken', 0)->paginate(10);

            if (!$requests) {
                return response()->json([
                    "error" => true,
                    "message" => "Product requests not found",
                    "data" => null
                ]);
            }
            return response()->json([
                "error" => false,
                "message" => null,
                "data" => $requests
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
    //CREATE NEW PRODUCT REQUEST
    public function store(Request $request)
    {
        $credentials = $request->all();

        $rules = [
            ['name' => 'required'],
            ['product_id' => 'required'],
        ];

        $validatorEmployee = Validator::make($credentials, $rules[0]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Request name is required.',
                'data' => null
            ], 422);
        }

        $validatorEmployee = Validator::make($credentials, $rules[1]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Product id is required.',
                'data' => null
            ], 422);
        }

        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();

            $product = Product::where('user_id', Auth::id())->first();

            if(!$product) {
                return response()->json([
                    'error'=> true,
                    'message'=> 'You have no product yet, go and create one before making requests.',
                    'data' => null
                ]);
            }

            $requests = ProductRequest::create([
                'name' => $request->name,
                'product_id' => $product->id,
            ]);
            if ($requests) {
                DB::commit();
                return response()->json([
                    "error" => false,
                    "message" => 'Request created successfully',
                    "data" => $requests
                ], 201);
            }
            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "Request could not be created",
                "data" => null
            ]);
        }else{
            return response()->json([
                "error" => true,
                "message" => "You are not a Product Owner",
                "data" => null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductRequest  $productRequest
     * @return \Illuminate\Http\Response
     */
    //DISPLAY A PRODUCT REQUEST
    public function show(ProductRequest $productRequest)
    {
        $requests = ProductRequest::with('product')->find($productRequest);
        if (!$requests) {
            return response()->json([
                "error" => true,
                "message" => "Request not found for this user",
                "data" => null
            ]);
        }
        return response()->json([
            "error" => false,
            "message" => null,
            "data" => $requests
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductRequest  $productRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductRequest $productRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductRequest  $productRequest
     * @return \Illuminate\Http\Response
     */
    //UPDATE A PRODUCT REQUEST
    public function update(Request $request, ProductRequest $productRequest)
    {
        $credentials = $request->all();

        $rules = [
            ['name' => 'required'],
            ['product_id' => 'required'],
        ];

        $validatorEmployee = Validator::make($credentials, $rules[0]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Request name is required.',
                'data' => null
            ]);
        }

        $validatorEmployee = Validator::make($credentials, $rules[1]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Product id is required.',
                'data' => null
            ]);
        }

        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();
            $product = Product::where('user_id', Auth::id())->first();

            if(!$product) {
                return response()->json([
                    'error'=> true,
                    'message'=> 'You have no product yet, go and create one before updating requests.',
                    'data' => null
                ]);
            }

            $requests = $productRequest->where('product_id', $product->id)->first();

            if (!$requests) {
                return response()->json([
                    "error" => true,
                    "message" => "Request not found for this user",
                    "data" => null
                ]);
            }

            $requests->update([
                'name' => $request->name,
                'product_id' => $product->id,
            ]);
            DB::commit();
            return response()->json([
                "error" => false,
                "message" => 'Request updated successfully',
                "data" => $requests
            ]);
        }else{
            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "You are not a Product Owner",
                "data" => null
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductRequest  $productRequest
     * @return \Illuminate\Http\Response
     */
    //DELETE A PRODUCT REQUEST
    public function destroy(ProductRequest $productRequest)
    {
        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();
            $product = Product::where('user_id', Auth::id())->first();

            if(!$product) {
                return response()->json([
                    'error'=> true,
                    'message'=> 'You have no product yet, go and create one before you delete requests.',
                    'data' => null
                ]);
            }

            $requests = ProductRequest::find($productRequest)->where('product_id', $product->id)->first();
            if (!$requests) {
                return response()->json([
                    "error" => true,
                    "message" => "Request not found for this user",
                    "data" => null
                ]);
            }
            $requests->delete();
            DB::commit();
            return response()->json([
                "error" => false,
                "message" => 'Request deleted successfully',
                "data" => null
            ], 204);
        }else{
            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "You are not a Product Owner",
                "data" => null
            ]);
        }
    }
}
