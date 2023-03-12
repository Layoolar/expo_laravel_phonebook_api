<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController as ApiController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
// use Validator;

class AuthController extends ApiController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:15', 'regex:/^\d+$/'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            'role' => ['required', 'string', 'in:admin,user'],
            'confirm_password' => 'required|same:password',
        ], [
            'phone.max' => 'The phone number must not exceed 15 digits',
            'phone.regex' => 'The phone number must only contain numbers',
            'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, one number, and one special character',
        ]);


        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['name'] =  $user->name;
        $success['id'] = $user->id;

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            /** @var \App\Models\MyUserModel $user **/
            $user = Auth::user();
            $tokenAbilities = $user->role === 'admin' ? ['admin'] : ['user'];
            $success['token'] =  $user->createToken('user_type', $tokenAbilities)->plainTextToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }


    // public function logout(Request $request)
    // {
    //     $token = str_replace('Bearer ', '', $request->header('Authorization'));

    //     if (Auth::user()) {
    //         Auth::user()->tokens()->where('token', $token)->delete();
    //     }

    //     return response()->json(['message' => 'User logged out successfully.']);
    // }


    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } else {
            return response()->json(['message' => 'No user found to log out'], 401);
        }
    }
}
