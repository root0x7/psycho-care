<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'first_name'          => fake()->firstName(),
            'second_name'         => fake()->lastName(),
            'email'               => fake()->unique()->safeEmail(),
            'login'               => fake()->unique()->userName(),
            'password'            => static::$password ??= Hash::make('password'),
            'gender'              => fake()->randomElement(['male', 'female']),
            'locale'              => fake()->randomElement(['en', 'ru', 'uz']),
            'timezone'            => 'UTC',
            'is_active'           => true,
            'registration_status' => 'pending',
            'remember_token'      => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_status' => 'pending',
        ]);
    }
}
