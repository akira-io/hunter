<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
final class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->optional()->sentence(3),
            'type' => $this->faker->randomElement(['direct', 'group']),
            'created_by' => User::factory(),
            'last_message_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'direct',
            'title' => null, // Direct conversations typically don't have titles
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'group',
            'title' => $this->faker->sentence(3),
        ]);
    }

    public function withParticipants(int $count = 2): static
    {
        return $this->afterCreating(function (Conversation $conversation) use ($count): void {
            $participants = User::factory()->count($count)->create();

            $pivotData = [];
            foreach ($participants as $index => $participant) {
                $pivotData[$participant->id] = [
                    'joined_at' => now()->subMinutes(random_int(1, 60)),
                    'last_read_at' => $this->faker->optional()->dateTimeBetween('-1 hour', 'now'),
                    'is_admin' => $index === 0, // First participant is admin
                ];
            }

            $conversation->participants()->attach($pivotData);
        });
    }

    public function withMessages(int $count = 5): static
    {
        return $this->afterCreating(function (Conversation $conversation) use ($count): void {
            $participants = $conversation->participants;

            if ($participants->isEmpty()) {
                // Create default participants if none exist
                $participants = User::factory()->count(2)->create();
                $conversation->participants()->attach($participants->mapWithKeys(fn ($user, $index): array => [
                    $user->id => [
                        'joined_at' => now()->subHour(),
                        'is_admin' => $index === 0,
                    ],
                ]));
            }

            \App\Models\Message::factory()
                ->count($count)
                ->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => fn () => $participants->random()->id,
                ]);

            // Update last_message_at
            $conversation->update([
                'last_message_at' => $conversation->messages()->latest()->first()?->created_at,
            ]);
        });
    }
}
