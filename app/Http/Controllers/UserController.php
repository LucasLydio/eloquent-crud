<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::with('profile')->get();
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        if (isset($data['profile'])) {
            $profile = Profile::create($data['profile']);
            $data['profile_id'] = $profile->id;
        }

        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        return $user->load('profile');
    }

    public function show($id)
    {
        return User::with('profile')->findOrFail($id);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        if (isset($data['profile'])) {
            if ($user->profile) {
                $user->profile->update($data['profile']);
            } else {
                $profile = Profile::create($data['profile']);
                $data['profile_id'] = $profile->id;
            }
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return $user->load('profile');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
