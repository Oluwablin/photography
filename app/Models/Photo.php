<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Photo extends Model
{
    use HasFactory;

    //Relationships
    /**
     * Photo belongs to a product.
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
