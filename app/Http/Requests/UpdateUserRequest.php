<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('user');

        return [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'sometimes|min:6',
            'profile.profile_name' => 'sometimes|string',
        ];
    }
}
