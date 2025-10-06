<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UserFollowedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $follower
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Don't send notification if there's a blocking relationship
        if ($notifiable instanceof User &&
            ($notifiable->hasBlocked($this->follower) || $notifiable->isBlockedBy($this->follower))) {
            return [];
        }

        $channels = [];

        /** @var array<string, mixed> $settings */
        $settings = $notifiable->notification_settings ?? [];

        if (($settings['follow_notifications'] ?? true) === true) {
            $channels[] = 'database';
        }

        if (($settings['browser_notifications'] ?? true) === true) {
            $channels[] = 'broadcast';
        }

        if (($settings['email_notifications'] ?? true) === true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Você tem um novo seguidor!')
            ->line("{$this->follower->name} (@{$this->follower->user_name}) começou a seguir você.")
            ->action('Ver perfil', route('public.profile.show', ['user' => $this->follower->id]))
            ->line('Continue construindo conexões incríveis!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'follow',
            'title' => 'Novo seguidor',
            'message' => "{$this->follower->name} começou a seguir você",
            'follower' => [
                'id' => $this->follower->id,
                'name' => $this->follower->name,
                'username' => $this->follower->user_name,
                'avatar_url' => $this->follower->avatar_url,
            ],
            'created_at' => now()->toISOString(),
            'read_at' => null,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'follow',
            'title' => 'Novo seguidor',
            'message' => "{$this->follower->name} começou a seguir você",
            'follower' => [
                'id' => $this->follower->id,
                'name' => $this->follower->name,
                'username' => $this->follower->user_name,
                'avatar_url' => $this->follower->avatar_url,
            ],
            'action_url' => route('public.profile.show', ['user' => $this->follower->id]),
        ];
    }
}
