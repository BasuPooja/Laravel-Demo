<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product; 
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

        return [
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->numberBetween(100, 5000),
            'description' => $this->faker->sentence(10),
            // 'image' =>'https://picsum.photos/640/480?random=' . rand(1, 10000),
            'image' =>'https://picsum.photos/640/480?random=' . rand(1, 10000),

        ];
    }
}
