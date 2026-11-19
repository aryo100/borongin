<?php

use App\Models\User;
use App\Services\OrderLifecycle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Validator;

Artisan::command('admin:create {email} {--name=Admin Borongin}', function (): int {
    $email = strtolower(trim($this->argument('email')));
    $name = $this->option('name');
    $validator = Validator::make(compact('email', 'name'), [
        'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
        'name' => ['required', 'string', 'max:120'],
    ]);
    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }

    $password = $this->secret('Kata sandi admin (minimal 12 karakter)');
    if (! is_string($password) || strlen($password) < 12 || strlen($password) > 72
        || $password !== $this->secret('Ulangi kata sandi')) {
        $this->error('Kata sandi atau konfirmasi tidak valid.');

        return 1;
    }

    $admin = new User(['name' => $name, 'email' => $email, 'password' => $password]);
    $admin->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();
    $this->info('Akun dibuat. Masuk dan siapkan autentikator di /account/security.');

    return 0;
})->purpose('Buat akun administrator Borongin');

Artisan::command('orders:expire', function (OrderLifecycle $orders): void {
    $this->info($orders->expireDue().' pesanan kedaluwarsa.');
})->purpose('Kedaluwarsakan pesanan belum bayar dan lepas reservasi');

Schedule::command('orders:expire')->everyMinute()->withoutOverlapping();
