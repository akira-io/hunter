<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
final class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->sentence(),
            'type' => 'text',
            'metadata' => null,
            'read_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 hour', 'now'),
        ];
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'text',
            'content' => $this->faker->sentence(),
            'metadata' => null,
        ]);
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'image',
            'content' => 'Image shared',
            'metadata' => [
                'filename' => $this->faker->word().'.jpg',
                'size' => $this->faker->numberBetween(1000, 5000000),
                'url' => $this->faker->imageUrl(),
            ],
        ]);
    }

    public function file(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'file',
            'content' => 'File shared',
            'metadata' => [
                'filename' => $this->faker->word().'.pdf',
                'size' => $this->faker->numberBetween(1000, 10000000),
                'mime_type' => 'application/pdf',
            ],
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => $this->faker->dateTimeBetween('-10 minutes', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }
}
