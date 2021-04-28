<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Photo;
use App\Models\ProductRequest;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [''];

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

    /**
     * Product belongs to a user.
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
