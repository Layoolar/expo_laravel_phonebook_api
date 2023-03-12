<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController as ApiController;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rule;


class UserController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'number' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     // $user = Auth::user(); // Get the authenticated user

    //     // $user = $user->users()->create($input);
    //     $User = User::create($input);

    //     return $this->sendResponse(new UserResource($User), 'User created successfully.');
    // }


    // public function store(Request $request)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'name' => ['required', 'string', 'max:255'],
    //         'username' => ['required', 'string', 'max:255', 'unique:users'],
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    //         'phone' => ['required', 'string', 'max:15', 'regex:/^\d+$/'],
    //         'password' => [
    //             'required',
    //             'string',
    //             'min:8',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
    //         ],
    //         'role' => ['required', 'string', 'in:admin,user'],
    //         'confirm_password' => 'required|same:password',
    //     ], [
    //         'phone.max' => 'The phone number must not exceed 15 digits',
    //         'phone.regex' => 'The phone number must only contain numbers',
    //         'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, one number, and one special character',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }
    //     $input = $request->all();
    //     $input['password'] = bcrypt($input['password']);

    //     $user = User::create($input);
    //     $success['name'] =  $user->name;
    //     $success['id'] = $user->id;
    //     return $this->sendResponse($success, 'User register successfully.');
    // }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function show(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        if ($user->id !== $request->user()->id) {
            return $this->sendError('Unauthorized.');
        }

        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
    }


    public function showAny($id)
    {
        $users = User::find($id);

        if (is_null($users)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($users), 'User retrieved successfully.');
    }


    // public function searchByName(Request $request)
    // {
    //     $name = $request->input('name');

    //     $users = User::where('name', 'LIKE', "%$name%")
    //         ->where('user_id', auth()->id())
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $users,
    //     ]);
    // }


    public function search($name)
    {
        $searchTerm = $name;
        $users = User::where('name', 'LIKE', "%$searchTerm%")
            ->orWhere('email', 'LIKE', "%$searchTerm%")
            ->orWhere('username', 'LIKE', "%$searchTerm%")
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }






    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, User $User)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'number' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     $User->name = $input['name'];
    //     $User->number = $input['number'];
    //     $User->save();

    //     return $this->sendResponse(new UserResource($User), 'User updated successfully.');
    // }





    public function update(Request $request, $id)
    {
        $rules = [
            'name' => ['string', 'max:255'],
            'username' => ['string', 'max:255', Rule::unique('users')->ignore($id)],
            'email' => ['string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => ['string', 'max:15', 'regex:/^\d+$/'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            'role' => ['string', 'in:admin,user'],
            'confirm_password' => 'nullable|same:password',
        ];

        $messages = [
            'phone.max' => 'The phone number must not exceed 15 digits',
            'phone.regex' => 'The phone number must only contain numbers',
            'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, one number, and one special character',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if ($user->id !== $request->user()->id) {
            return $this->sendError('Unauthorized.');
        }

        if ($user) {
            $input = $request->all();
            if ($request->has('password')) {
                $input['password'] = bcrypt($input['password']);
            }

            $user->update($input);
            return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        } else {
            return $this->sendError('Unable to update.');
        }
    }

    public function updateAny(Request $request, $id)
    {
        $rules = [
            'name' => ['string', 'max:255'],
            'username' => ['string', 'max:255', Rule::unique('users')->ignore($id)],
            'email' => ['string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => ['string', 'max:15', 'regex:/^\d+$/'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            'role' => ['string', 'in:admin,user'],
            'confirm_password' => 'nullable|same:password',
        ];

        $messages = [
            'phone.max' => 'The phone number must not exceed 15 digits',
            'phone.regex' => 'The phone number must only contain numbers',
            'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, one number, and one special character',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if ($user) {
            $input = $request->all();
            if ($request->has('password')) {
                $input['password'] = bcrypt($input['password']);
            }

            $user->update($input);
            return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        } else {
            return $this->sendError('Unable to update.');
        }
    }






    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return $this->sendResponse(new UserResource($user), 'Deleted successfully.');
        } else {
            return $this->sendError('Unable to delete.');
        }
    }
}
