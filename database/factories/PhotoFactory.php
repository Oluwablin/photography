<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'product_photo' => $this->faker->image('public/images',400,300, null, false),
        ];
    }
}
