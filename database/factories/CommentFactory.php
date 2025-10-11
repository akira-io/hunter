<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
final class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'commentable_id' => Hunt::factory(),
            'commentable_type' => Hunt::class,
            'commenter_id' => User::factory(),
            'commenter_type' => User::class,
            'content' => fake()->paragraph(),
            'approved' => true,
            'reply_id' => null,
        ];
    }

    public function forHunt(Hunt $hunt): static
    {
        return $this->state(fn (array $attributes): array => [
            'commentable_id' => $hunt->id,
            'commentable_type' => Hunt::class,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'commenter_id' => $user->id,
            'commenter_type' => User::class,
        ]);
    }
}
