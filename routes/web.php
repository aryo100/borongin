<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::view('/account/profile', 'store.profile')->name('profile.edit');
    Route::patch('/account/profile', function (Request $request) {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $request->user()->update($data);

        return to_route('profile.edit')->with('status', 'Profil disimpan.');
    })->name('profile.update');
});

Route::view('/account/security', 'auth.security')
    ->middleware(['auth', 'verified', 'password.confirm'])
    ->name('security');

require __DIR__.'/store.php';
require __DIR__.'/admin.php';
