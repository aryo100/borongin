<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\TwoFactorLoginResponse;

class TwoFactorResponse implements TwoFactorLoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();
        if ($user?->role === 'admin') {
            $request->session()->put('admin_mfa_user', $user->getKey());
        }

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($user?->role === 'admin' ? '/admin' : '/');
    }
}
