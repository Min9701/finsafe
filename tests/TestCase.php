<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set the currently logged in user for the application.
     *
     * @param  string|null  $guard
     * @return $this
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        parent::actingAs($user, $guard);

        if ($user instanceof User && $user->totp_secret !== null && $user->totp_verified_at !== null) {
            $this->withSession(['auth.mfa_verified_user_id' => $user->id]);
        }

        return $this;
    }

    /**
     * Set the currently logged in user without MFA session verification.
     *
     * @param  string|null  $guard
     * @return $this
     */
    public function actingAsWithoutMfa(Authenticatable $user, $guard = null)
    {
        parent::actingAs($user, $guard);

        $this->withSession(['auth.mfa_verified_user_id' => null]);

        return $this;
    }
}
