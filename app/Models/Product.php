<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'image'];

    protected $casts = [
        'price' => 'float',
    ];

    // public function getImageAttribute($value)
    // {
    //     if (!$value) {
    //         return null;
    //     }

    //     if (str_starts_with($value, 'http')) {
    //         return $value;
    //     }

    //     return asset('storage/' . $value);
    // }
}
