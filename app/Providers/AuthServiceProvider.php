<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\TwoFactorResponse;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Fortify;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TwoFactorLoginResponse::class, TwoFactorResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::loginView(fn () => view('auth.form', ['screen' => 'login']));
        Fortify::registerView(fn () => view('auth.form', ['screen' => 'register']));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.form', ['screen' => 'reset-link']));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.form', ['screen' => 'reset', 'request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.form', ['screen' => 'verify']));
        Fortify::confirmPasswordView(fn () => view('auth.form', ['screen' => 'confirm']));
        Fortify::twoFactorChallengeView(fn () => view('auth.form', ['screen' => 'challenge']));

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower($request->string(Fortify::username())->limit(256)->toString()).'|'.$request->ip()),
            Limit::perMinute(30)->by('ip:'.$request->ip()),
        ]);
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by(
            $request->session()->get('login.id').'|'.$request->ip()
        ));
        Event::listen(Login::class, function (): void {
            session()->forget('admin_mfa_user');
        });
    }
}
