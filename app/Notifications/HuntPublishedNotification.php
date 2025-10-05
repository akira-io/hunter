<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class HuntPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Hunt $hunt,
        private readonly User $author
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
        $channels = [];

        /** @var array<string, mixed> $settings */
        $settings = $notifiable->notification_settings ?? [];

        if (($settings['hunt_notifications'] ?? true) === true) {
            $channels[] = 'database';
        }

        if (($settings['browser_notifications'] ?? true) === true) {
            $channels[] = 'broadcast';
        }

        if (($settings['email_notifications'] ?? false) === true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $contentPreview = mb_strlen($this->hunt->content) > 100
            ? mb_substr($this->hunt->content, 0, 100).'...'
            : $this->hunt->content;

        return (new MailMessage)
            ->subject("{$this->author->name} publicou um novo hunt!")
            ->line("{$this->author->name} (@{$this->author->user_name}) acabou de publicar um novo hunt.")
            ->line($contentPreview)
            ->action('Ver Hunt', route('hunts.show', ['hunt' => $this->hunt->id]))
            ->line('Continue acompanhando os melhores conteúdos!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $contentPreview = mb_strlen($this->hunt->content) > 100
            ? mb_substr($this->hunt->content, 0, 100).'...'
            : $this->hunt->content;

        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'hunt_published',
            'title' => 'Novo Hunt',
            'message' => "{$this->author->name} publicou um novo hunt",
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'username' => $this->author->user_name,
                'avatar_url' => $this->author->avatar_url,
            ],
            'hunt' => [
                'id' => $this->hunt->id,
                'content_preview' => $contentPreview,
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
        $contentPreview = mb_strlen($this->hunt->content) > 100
            ? mb_substr($this->hunt->content, 0, 100).'...'
            : $this->hunt->content;

        return [
            'type' => 'hunt_published',
            'title' => 'Novo Hunt',
            'message' => "{$this->author->name} publicou um novo hunt",
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'username' => $this->author->user_name,
                'avatar_url' => $this->author->avatar_url,
            ],
            'hunt' => [
                'id' => $this->hunt->id,
                'content_preview' => $contentPreview,
            ],
            'action_url' => route('hunts.show', ['hunt' => $this->hunt->id]),
        ];
    }
}
