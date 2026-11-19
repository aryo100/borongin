<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    public function reset($user, array $input): void
    {
        $validated = Validator::make($input, [
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(12)],
        ])->validate();

        $user->update(['password' => $validated['password']]);
    }
}
