<?php

//app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'category_id',
        'description',
        'price',
        'stock_quantity',
        'origin',
        'roast_level',
        'flavor_notes',
    ];

    protected $casts = [
        'flavor_notes' => 'json',
    ];
}
