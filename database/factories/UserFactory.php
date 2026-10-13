<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'USER',
            'status' => 'ACTIVE',
            'totp_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Keep the legacy factory state name available for existing tests.
     */
    public function unverified(): static
    {
        return $this->unverifiedTotp();
    }

    public function unverifiedTotp(): static
    {
        return $this->state(fn (array $attributes) => [
            'totp_secret' => null,
            'totp_verified_at' => null,
        ]);
    }
}
