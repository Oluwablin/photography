<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->unique()->word(2);
        $slug = Str::slug($title, '.');
        $description = $title . ' role';

        return [
            'name' => $title,
            'slug' => $slug,
            'description' => $description,
            'level' => $this->faker->unique()->numberBetween(1, 5),
        ];
    }
}
