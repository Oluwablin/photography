<?php

namespace App\Http\Controllers\v1\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Validator, Hash, DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserMail;
class RegisterController extends Controller
{
     /**
     * Create new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $credentials = $request->only('firstname', 'lastname', 'email', 'user_role', 'password', 'password_confirmation');

        $rules = [
            ['firstname' => 'required'],
            ['lastname' => 'required'],
            ['email' => 'required'],
            ['user_role' => 'required'],
            ['password'     => 'required'],
            ['password'     => 'min:4'],
            ['password'     => 'confirmed'],
        ];

        $mailExists = User::where('email', $request->email)->first();
        // Checkout if emaill already exists
        if ($mailExists) {
            return response()->json([
                'error' => true,
                'message' => 'Email already exists in the system',
                'data' => null
            ], 403);
        }

        $validatorName = Validator::make($credentials, $rules[0]);
        if($validatorName->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Firstname is required.',
                'data' => null
            ]);
        }

        $validatorName = Validator::make($credentials, $rules[1]);
        if($validatorName->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Lastname is required.',
                'data' => null
            ]);
        }

        $validatorEmail = Validator::make($credentials, $rules[2]);
        if($validatorEmail->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'Email is required.',
                'data' => null
            ]);
        }

        $validatorRole = Validator::make($credentials, $rules[3]);
        if($validatorRole->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> 'User role is required.',
                'data' => null
            ]);
        }

        $validatorPassword = Validator::make($credentials, $rules[4]);
        if($validatorPassword->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> $validatorPassword->messages()->all(),
                'data' => null
            ], 422);
        }

        $validatorPassword = Validator::make($credentials, $rules[5]);
        if($validatorPassword->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> $validatorPassword->messages()->all(),
                'data' => null
            ], 422);
        }

        $validatorPassword = Validator::make($credentials, $rules[6]);
        if($validatorPassword->fails()) {
            return response()->json([
                'error'=> true,
                'message'=> $validatorPassword->messages()->all(),
                'data' => null
            ], 422);
        }

        DB::beginTransaction();

            /** CREATE NEW USER **/
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Assign user a role
            $user->attachRole($request->user_role);


            $data = [
                'email' => $request->email,
                'firstname' => $request->firstname,
                'password'  => $request->password,
                'subject' => "Account Creation.",
            ];

            Notification::send($user, new NewUserMail($data));

            DB::commit();

            return response()->json([
                'error'=> false,
                'message'=> 'User account created successfully, Please check your mail :' . $user->email . ' for more details',
                'data' => null
            ], 201);

        if (!$user) {
            DB::rollBack();
            return response()->json([
                'error'=> true,
                'message'=> 'User was not created',
                'data' => null
            ]);
        }

    }
}
