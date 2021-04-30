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
use App\Notifications\NewUserMail;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;

class AuthenticationTest extends TestCase
{
    use HasFactory, WithFaker;
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

    public function test_it_cannot_register_user_without_payload()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $request_data = [];

        $response = $this->json('POST', '/api/v1/auth/create', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_it_cannot_login_user_without_payload()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $request_data = [];

        $response = $this->json('POST', '/api/v1/auth/login', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_it_can_register_user()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $firstname = $this->faker->name;
        $lastname = $this->faker->name;
        $email = $this->faker->safeEmail;
        $password = $this->faker->password(8);
        $user_role = $role->id;

        $request_data = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'user_role' => $user_role,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->json('POST', '/api/v1/auth/create', $request_data, ['Accept' => 'application/json']);
        //dd($response);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
        ]);
        $response->assertJson([
            "error" => false,
            'message'=> 'User account created successfully, Please check your mail :' . $email . ' for more details',
            "data" => [],
        ]);
    }

    public function test_it_can_login_user()
    {
        $firstname = $this->faker->name;
        $lastname = $this->faker->name;
        $email = $this->faker->safeEmail;
        $password = $this->faker->password(8);

        $user = User::factory()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => bcrypt($password),
            'remember_token' => Str::random(10),
        ]);

        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);

        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $request_data = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->json('POST', '/api/v1/auth/login', $request_data, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
        $this->assertArrayHasKey('data', $response->json());
        $response->assertJson([
            "error" => false,
            'message'=> 'You are logged in successfully',
            "data" => [],
        ]);
    }

    public function test_it_can_logout_user()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);

        $user->attachRole($role);
        $this->actingAs($user, 'api');
        $token = JWTAuth::fromUser($user);

        $response = $this->json('POST', '/api/v1/logout?token=' . $token, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            'message'=> 'Successfully logged out',
            "data" => [],
        ]);
    }

    public function test_it_can_get_authenticated_user()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);

        $user->attachRole($role);
        $this->actingAs($user, 'api');
        $token = JWTAuth::fromUser($user);

        $response = $this->json('GET', '/api/v1/me?token=' . $token, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson([
            "error" => false,
            'message'=> null,
            "data" => [],
        ]);
    }

    public function test_user_can_get_mail_after_registering()
    {
        Notification::fake();
        $firstname = $this->faker->name;
        $lastname = $this->faker->name;
        $email = $this->faker->safeEmail;
        $password = $this->faker->password(8);

        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => 'Photographer',
            "slug" => "photographer",
            "description" => "Photographer Role",
            "level" => 4,
        ]);
        $user_role = $role->id;

        $user->attachRole($role);
        $this->actingAs($user, 'api');

        $request_data = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'user_role' => $user_role,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->json('POST', '/api/v1/auth/create', $request_data, ['Accept' => 'application/json']);
        //dd($response);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
        ]);
        $response->assertJson([
            "error" => false,
            'message'=> 'User account created successfully, Please check your mail :' . $email . ' for more details',
            "data" => [],
        ]);

        $user_email = User::where('email', $email)->first();

        Notification::assertSentTo(
            [$user_email], NewUserMail::class
        );
    }
}
