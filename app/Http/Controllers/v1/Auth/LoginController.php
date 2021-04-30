<?php

namespace App\Http\Controllers\v1\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Validator, Hash, DB, Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $rules = [
            ['email' => 'required'],
            ['password'     => 'required'],
        ];

        $validatorName = Validator::make($credentials, $rules[0]);
        if($validatorName->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'email is required.',
                'data' => null
            ], 422);
        }

        $validatorName = Validator::make($credentials, $rules[1]);
        if($validatorName->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'password is required.',
                'data' => null
            ], 422);
        }

        // Check if email exists on the platform
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'You have not yet registered on this platform',
                'data' => null
            ], 401);
        }


        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => true,
                'message' => 'Incorrect email or password',
                'data' => null
            ], 401);
        }

        if ($user->is_active !== 1) {
            return response()->json([
                'error' => true,
                'message' => 'You are no longer active on this platform',
                'data' => null
            ], 401);
        }

        $data = [
            'user' => Auth::user(),
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ];


        return response()->json([
            'error' => false,
            'message' => 'You are logged in successfully',
            'data' => $data
        ]);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json([
            'error' => false,
            'message' => null,
            'data' => $user
        ]);
    }

    /**
     * Log the user out.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();


        return response()->json([
            'error' => false,
            'message' => 'Successfully logged out',
            'data' => null
        ]);
    }
}
