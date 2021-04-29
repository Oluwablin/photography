<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\TestResponse;

class ProductOwnerTest extends TestCase
{
    use HasFactory;
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test it can create a product*/
    public function product_owner_can_create_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        //$product = Product::factory()->create();

        $product_data = [
            "name" => "Test name",
            "user_id" => 1,
        ];

        $response = $this->json('POST', '/api/v1/product/add/new', $product_data, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        $response->assertJson([
            "error" => false,
            "message" => 'Product created successfully',
            "data" => [
                "name" => "Test name",
                "user_id" => 1,
            ],
        ]);
    }

    /** @test to see a sinsle product */
    public function it_can_see_a_product()
    {
        $this->withoutMiddleware();
        $product = Product::factory()->count(2)->create();
        $response = $this->get('/api/v1/product/fetch/one/{product}');
        $response->assertStatus(200);
        $response->decodeResponseJson($product);
    }

    /** @test to see all products*/
    public function it_can_see_all_products()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $product = Product::factory()->count(2)->create();
        $response = $this->get('/api/v1/product/fetch/all');
        $response->assertStatus(200);
        $response->decodeResponseJson($product);
    }

    /** @test to update all products*/
    public function it_can_update_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $product = Product::factory()->count(2)->create();
        $response = $this->put('/api/v1/product/update/{product}');
        $response->assertStatus(200);
        $response->decodeResponseJson($product);
    }

    /** @test to update all products*/
    public function it_can_delete_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $product = Product::factory()->count(2)->create();
        $response = $this->delete('/api/v1/product/delete/{product}');
        $response->assertStatus(200);
        $response->decodeResponseJson($product);
    }

    /** @test to seed database*/
    public function test_roles_and_permissions_are_seeded()
    {
        // Run the DatabaseSeeder...
        $this->seed();

    }
}
