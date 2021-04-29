<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithoutMiddleware;

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

    /** @test */
    public function it_can_create_a_product_owner()
    {
        $product = Product::factory()->count(5)->create();

        //$this->actingAs($this->user);
        $this->withoutMiddleware();
        $response = $this->post('/api/v1/product/add/new');
        $response->assertStatus(200);
        $response->assertSee('name');
    }
}
