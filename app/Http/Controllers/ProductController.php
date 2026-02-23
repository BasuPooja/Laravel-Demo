<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProductRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('products', 'public');
            $product->image = $image;
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('minPrice')) {
            $query->where('price', '>=', $request->minPrice);
        }

        if ($request->filled('maxPrice')) {
            $query->where('price', '<=', $request->maxPrice);
        }

        if ($request->sort === 'price_low') {
            $query->orderBy('price', 'asc');
        }

        if ($request->sort === 'price_high') {
            $query->orderBy('price', 'desc');
        }

        if ($request->sort === 'latest') {
            $query->latest();
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->sortField && $request->sortDirection) {
            $query->orderBy($request->sortField, $request->sortDirection);
        }

        $products = $query->paginate(8)->withQueryString();

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');  
        // $path = $request->file('image')->store('products', 'public');
            // $data['image'] = $path;
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            // 'data' => $product->fresh()
            'data' => $product, 
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

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json([
                'message' => 'Invalid product IDs'
            ], 400);
        }

        Product::whereIn('id', $ids)->delete();

        return response()->json([
            'message' => 'Selected products deleted successfully'
        ]);
    }

    public function export(Request $request)
    {
        $query = Product::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Export selected IDs
        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        // Export current page
        if ($request->filled('page')) {
            $perPage = 8;
            $query->skip(($request->page - 1) * $perPage)
                ->take($perPage);
        }

        $products = $query->get();

        $response = new StreamedResponse(function () use ($products) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Name', 'Price', 'Description', 'Image']);

            foreach ($products as $product) {
            // Export image full path 
            $imageUrl = $product->image
                    ? asset('storage/' . $product->image)
                    : '';

                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->price,
                    $product->description,
                    $imageUrl 
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="products.csv"'
        );

        return $response;
    }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:csv,txt|max:2048'
    //     ]);

    //     $file = $request->file('file');
    //     $path = $file->getRealPath();

    //     $handle = fopen($path, 'r');

    //     // Skip header row
    //     fgetcsv($handle);

    //     DB::beginTransaction();

    //     try {
    //         while (($row = fgetcsv($handle, 1000, ',')) !== false) {

    //             Product::create([
    //                 'name' => $row[0],
    //                 'price' => $row[1],
    //                 'description' => $row[2] ?? null,
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Products imported successfully'
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollback();

    //         return response()->json([
    //             'message' => 'Import failed',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:4096'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        fgetcsv($handle);

        DB::beginTransaction();

        try {

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                $imagePath = null;

                if (!empty($row[4])) {

                    $filename = basename($row[4]);

                    $imagePath = 'products/' . $filename;
                }

                Product::create([
                    'name' => $row[1],
                    'price' => $row[2],
                    'description' => $row[3] ?? null,
                    'image' => $imagePath
                ]);
            }

            fclose($handle);

            DB::commit();

            return response()->json([
                'message' => 'Products imported successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
