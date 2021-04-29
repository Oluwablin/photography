<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\TestResponse;

class ProductRequestTest extends TestCase
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
    public function it_does_not_create_a_product_request_without_payload ()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $product = Product::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $request_data = [];

        $response = $this->json('POST', '/api/v1/product_request/add/new', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    /** @test it can create a request*/
    public function request_owner_can_create_a_request()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create(
            [
                "name" => "Test Product",
                "user_id" => $user->id,
            ]
        );

        $request_data = [
            "name" => "Test request",
            "product_id" => $product->id,
        ];

        $response = $this->json('POST', '/api/v1/product_request/add/new', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        $response->assertJson([
            "error" => false,
            "message" => 'Request created successfully',
            "data" => [
                "name" => "Test request",
                "product_id" => $product->id,
            ],
        ]);
    }

    /** @test to see a single request */
    public function it_can_see_a_request()
    {
        $this->withoutMiddleware();
        $request = ProductRequest::factory()->count(2)->create();
        $response = $this->get('/api/v1/product_request/fetch/one/{request}');
        $response->assertStatus(200);
        $response->decodeResponseJson($request);
    }

    /** @test to see all requests*/
    public function it_can_see_all_requests()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $request = ProductRequest::factory()->count(2)->create();
        $response = $this->get('/api/v1/product_request/fetch/all');
        $response->assertStatus(200);
        $response->decodeResponseJson($request);
    }

    /** @test to update all requests*/
    public function it_can_update_a_request()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $request = ProductRequest::factory()->count(2)->create();
        $response = $this->put('/api/v1/product_request/update/{request}');
        $response->assertStatus(200);
        $response->decodeResponseJson($request);
    }

    /** @test to update all requests*/
    public function it_can_delete_a_request()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->is('product.owner');
        $this->actingAs($user, 'api');

        $request = ProductRequest::factory()->count(2)->create();
        $response = $this->delete('/api/v1/product_request/delete/{request}');
        $response->assertStatus(200);
        $response->decodeResponseJson($request);
    }

}
