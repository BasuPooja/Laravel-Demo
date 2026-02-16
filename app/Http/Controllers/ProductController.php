<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProductRequest;


class ProductController extends Controller
{
    public function index()
    {
        $query = Product::query();

        if(request->filled('search')){
            $query->where('name','like','%'.request('search').'%');
        }

        if ($request->filled('sort')) {
        $allowedSorts = ['name', 'price', 'created_at'];

        if (in_array($request->sort, $allowedSorts)) {
            $query->orderBy($request->sort, 'asc');
        }
    }

        $products = $query->paginate(8)->withQueryString();

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product, 200);
        
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);        
    }

    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
