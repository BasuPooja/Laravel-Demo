<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Product::class;

    public function definition(): array
    {
        // $path = storage_path('app/public/products');

        // if (!file_exists($path)) {
        //     mkdir($path, 0777, true);
        // }

        // $image = $this->faker->image(
        //     storage_path('app/public/products'),
        //     640,
        //     480,
        //     null,
        //     false 
        // );

        // Generate random image ID (stable image)
        $imageId = "https://picsum.photos/640/480";

        // Fetch image from picsum
        $imageContents = file_get_contents($imageId);

        // Generate unique filename
        $fileName = Str::uuid() . '.jpg';

        // Store inside storage/app/public/products
        Storage::disk('public')->put("products/{$fileName}", $imageContents);

        return [
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->numberBetween(100, 5000),
            'description' => $this->faker->sentence(10),
            // 'image' =>'https://picsum.photos/640/480?random=' . rand(1, 10000),
            // 'image' => 'https://picsum.photos/id/' . rand(1, 1000) . '/640/480',
            // 'image' => $this->faker->imageUrl(640, 480, 'products', true),
            'image' => "products/{$fileName}",

        ];
    }
}
