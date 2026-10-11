<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_account_status(): void
    {
        $user = User::create([
            'username' => 'admin01',
            'password' => 'password',
            'role' => 'hr',
            'status' => 'locked',
        ]);

        $this->assertSame('locked', $user->fresh()->status);
        $this->assertTrue($user->isLocked());
    }
}
