<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMfaTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_needs_mfa_for_each_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertRedirect(route('security'));
        $admin->forceFill([
            'two_factor_secret' => encrypt('TEST-SECRET'),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $this->get('/admin')->assertRedirect(route('security'));
        $this->withSession(['admin_mfa_user' => $admin->id])->get('/admin')->assertOk();
    }
}
