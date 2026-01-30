<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::all();
        // return "Index";
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     // return "Create";
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string',
    //         'price' => 'required|numeric'
    //     ]);

    //     Product::create($data);

    //     return response()->json(['message' => 'Product created'], 201);
    // }


    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->only(['name', 'price', 'description']));
        return response()->json(['message' => 'Product updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Product::destroy($id);
        return "Product deleted";
    }
}
