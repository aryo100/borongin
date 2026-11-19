<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        if (is_string($input['email'] ?? null)) {
            $input['email'] = strtolower(trim($input['email']));
        }

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(12)],
        ])->validate();

        return User::create($validated);
    }
}
