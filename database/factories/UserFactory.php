<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => fake()->unique()->numerify('01#########'),
            'profile_picture' => null,
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::JobSeeker,
            'status' => AccountStatus::Active,
            'deletion_requested_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Suspended,
        ]);
    }

    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Deactivated,
        ]);
    }
}
