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

    /** @test it cannot create a product without payload*/
    public function it_does_not_create_a_product_without_payload ()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product_data = [];

        $response = $this->json('POST', '/api/v1/product/add/new', $product_data, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    /** @test it can create a product*/
    public function product_owner_can_create_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product_data = [
            "name" => "Test name",
            "user_id" => $user->id,
        ];

        $response = $this->json('POST', '/api/v1/product/add/new', $product_data, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        $response->assertJson([
            "error" => false,
            "message" => 'Product created successfully',
            "data" => [
                "name" => "Test name",
                "user_id" => $user->id,
            ],
        ]);
    }

    /** @test to see a single product */
    public function it_can_see_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create([
            "name" => "Test Product",
            "user_id" => $user->id,
        ]);
        $response = $this->get('/api/v1/product/fetch/one/' . $product->id, [], ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            "message" => '',
            "data" => [
                "name" => "Test Product",
                "user_id" => $user->id,
            ],
        ]);
    }

    /** @test to see all products*/
    public function it_can_see_all_products()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create([
            "name" => "Test Product",
            "user_id" => $user->id,
        ]);

        $response = $this->get('/api/v1/product/fetch/all?page=1', ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJsonFragment(['current_page' => 1]);

    }

    /** @test to update all products*/
    public function it_can_update_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create([
            "name" => "Test Product",
            "user_id" => $user->id,
        ]);
        $updatedProduct = [
            'name' => 'Updated Product',
            'user_id' => $user->id,
        ];

        $response = $this->json('PUT', '/api/v1/product/update/{$product->id}', $updatedProduct, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            "message" => 'Product updated successfully',
            "data" => [
                "name" => "Updated Product",
                "user_id" => $user->id,
            ],
        ]);
    }

    /** @test to update all products*/
    public function it_can_delete_a_product()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create(
            [
                "name" => "Test",
                "user_id" => $user->id,
            ]
        );

        $response = $this->delete('/api/v1/product/delete/{$product->id}', [], ['Accept' => 'application/json']);
        $response->assertStatus(204);
        $this->assertCount(0, Product::all());
    }

    /** @test to seed database*/
    public function test_roles_and_permissions_are_seeded()
    {
        // Run the DatabaseSeeder...
        $this->seed();

    }
}
