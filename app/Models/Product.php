<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Photo;
use App\Models\ProductRequest;

class Product extends Model
{
    use HasFactory;

    //Relationships
    /**
     * Product has many Photos.
     *
     * @return mixed
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Product has many product requests.
     *
     * @return mixed
     */
    public function product_requests()
    {
        return $this->hasMany(ProductRequest::class);
    }
}
