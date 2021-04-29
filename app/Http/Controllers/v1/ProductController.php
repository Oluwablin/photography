<?php

namespace App\Http\Controllers\v1;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Validator, DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //DISPLAY ALL PRODUCTS
    public function index()
    {
        if(Auth::user()->hasRole('product.owner')){
            $products = Product::with('user')->paginate(10);

            if (!$products) {
                return response()->json([
                    "error" => true,
                    "message" => "Products not found",
                    "data" => null
                ]);
            }
            return response()->json([
                "error" => false,
                "message" => null,
                "data" => $products
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
    //CREATE NEW PRODUCT
    public function store(Request $request)
    {
        $credentials = $request->all();

        $rules = [
            ['name' => 'required'],
        ];

        $validatorEmployee = Validator::make($credentials, $rules[0]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Product name is required.',
                'data' => null
            ], 422);
        }
        if(Auth::user()->hasRole('product.owner')){
            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'user_id' => Auth::id(),
            ]);
            if ($product) {
                DB::commit();
                return response()->json([
                    "error" => false,
                    "message" => 'Product created successfully',
                    "data" => $product
                ], 201);
            }
            DB::rollback();
            return response()->json([
                "error" => true,
                "message" => "Product could not be created",
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
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    //DISPLAY A PRODUCT
    public function show(Product $product)
    {
        $products = $product->with('user')->where('user_id', Auth::id())->first();
        if (!$products) {
            return response()->json([
                "error" => true,
                "message" => "Product not found for this user",
                "data" => null
            ]);
        }
        return response()->json([
            "error" => false,
            "message" => null,
            "data" => $products
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    //UPDATE A PRODUCT
    public function update(Request $request, Product $product)
    {
        $credentials = $request->all();

        $rules = [
            ['name' => 'required'],
        ];

        $validatorEmployee = Validator::make($credentials, $rules[0]);
        if($validatorEmployee->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Product name is required.',
                'data' => null
            ], 422);
        }

        $products = $product->where('user_id', Auth::id())->first();
        if (!$products) {
            return response()->json([
                "error" => true,
                "message" => "Product not found for this user",
                "data" => null
            ]);
        }
        $products->update([
            'name' => $request->name,
            'user_id' => Auth::id(),
        ]);
        return response()->json([
            "error" => false,
            "message" => 'Product updated successfully',
            "data" => $products
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    //DELETE A PRODUCT
    public function destroy(Product $product)
    {
        $products = $product->where('user_id', Auth::id())->first();
        if (!$products) {
            return response()->json([
                "error" => true,
                "message" => "Product not found for this user",
                "data" => null
            ]);
        }
        $products->delete();
        return response()->json([
            "error" => false,
            "message" => 'Product deleted successfully',
            "data" => null
        ], 204);
    }
}
