<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductRequest;
use App\Models\Photo;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PhotoFromPhotographer;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Str;

class PhotoTest extends TestCase
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

    /** @test it cannot create a photo without payload*/
    public function it_does_not_create_a_photo_without_payload()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');
        $photo = Photo::factory()->create();

        $request_data = [];

        $response = $this->json('POST', '/api/v1/photo/add/new', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    /** @test it can create a photo*/
    public function photographer_can_create_a_photo()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create(
            [
                "name" => "Test Product",
                "user_id" => $user->id,
            ]
        );

        $request = ProductRequest::factory()->create([
            "name" => "photo request",
            "product_id" => $product->id,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('photo1.jpg');
        $fileExt = $file->getClientOriginalExtension();
            $name =  Str::upper('photo1').'_'.date("Y-m-d").'_'.time().'.'.$fileExt;
            $attachmentName = config('app.url').'/'.'images/'.$name;

        $request_data = [
            "product_photo" => $attachmentName,
            "product_id" => $product->id,
        ];

        $response = $this->json('POST', '/api/v1/photo/add/new', ['product_photo' => $file, "product_id" => $product->id], $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        //dd($response);
        $response->assertJson([
            "error" => false,
            "message" => 'Photo created successfully',
            "data" => [
                "product_photo" => $attachmentName,
                "product_id" => $product->id,
            ],
        ]);
    }

    /** @test to see all photos*/
    public function it_can_see_all_photos()
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

        $request = Photo::factory()->create([
            "product_photo" => "photo.jpg",
            "product_id" => $product->id,
        ]);

        $response = $this->get('/api/v1/photo/fetch/all', ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJsonFragment(['per_page' => 10]);
    }

    /** @test Assert a notification was sent to the given product owner*/
    public function it_sent_mail_product_owner()
    {

        $this->withoutMiddleware();
        Notification::fake();
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create(
            [
                "name" => "Test Product",
                "user_id" => $user->id,
            ]
        );

        $request = ProductRequest::factory()->create([
            "name" => "photo request",
            "product_id" => $product->id,
        ]);

        $photo = Photo::factory()->create(
            [
                "product_photo" => \Illuminate\Http\UploadedFile::fake()->image('photo1.jpg'),
                "product_id" => $product->id,
            ]
        );

        $request_data = [
            "product_photo" => \Illuminate\Http\UploadedFile::fake()->image('photo1.jpg'),
            "product_id" => $product->id,
        ];

        $file = \Illuminate\Http\UploadedFile::fake()->image('photo1.jpg');

        $response = $this->json('POST', '/api/v1/photo/add/new', ['product_photo' => $file, "product_id" => $product->id], [], $request_data, ['Accept' => 'application/json']);
        //dd($response);
        $response->assertStatus(201);

        Notification::assertSentTo(
            [$user], PhotoFromPhotographer::class
        );

    }

    /** @test to see all photos*/
    public function it_can_approve_photos()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Product Owner',
            "slug" => "product.owner",
            "description" => "Product Owner Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');
        $product = Product::factory()->create([
            "name" => "photo jpg",
            "user_id" => $user->id,
        ]);

        $request = ProductRequest::factory()->create([
            "name" => "photo request",
            "product_id" => $product->id,
        ]);

        $photo = Photo::factory()->create([
            "product_photo" => "photorequest.jpg",
            "product_id" => $product->id,
        ]);

        $request_data = [
            "product_photo" => "photorequest.jpg",
            "product_id" => $product->id,
        ];

        $response = $this->json('POST', '/api/v1/photo/approve', $request_data, ['Accept' => 'application/json']);
        //dd($response);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            "message" => 'Photo Approved',
            "data" => [
                "product_photo" => "photorequest.jpg",
                "product_id" => $product->id,
            ],
        ]);
    }

    /** @test to see all photos*/
    public function it_can_reject_photos()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Product Owner',
            "slug" => "product.owner",
            "description" => "Product Owner Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $product = Product::factory()->create([
            "name" => "photo jpg",
            "user_id" => $user->id,
        ]);

        $request = ProductRequest::factory()->create([
            "name" => "photo request",
            "product_id" => $product->id,
        ]);

        $photo = Photo::factory()->create([
            "product_photo" => "photorequest.jpg",
            "product_id" => $product->id,
        ]);

        $request_data = [
            "product_photo" => "photo.jpg",
            "product_id" => $product->id,
        ];

        $response = $this->json('POST', '/api/v1/photo/reject', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            "message" => 'Photo Rejected',
            "data" => [],
        ]);
    }
}
