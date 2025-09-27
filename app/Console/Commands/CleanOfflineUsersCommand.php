<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\UserOffline;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class CleanOfflineUsersCommand extends Command
{
    protected $signature = 'chat:clean-offline-users';

    protected $description = 'Clean up offline users and broadcast offline events';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $onlineUsers = [];
        $offlineUsers = [];

        User::chunk(100, function ($users) use (&$onlineUsers, &$offlineUsers): void {
            foreach ($users as $user) {
                $cacheKey = "user_online_{$user->id}";
                $lastSeen = Cache::get($cacheKey);

                if ($lastSeen && now()->diffInMinutes($lastSeen) <= 5) {
                    $onlineUsers[] = $user->id;
                } elseif ($lastSeen) {
                    $offlineUsers[] = $user;
                    Cache::forget($cacheKey);
                }
            }
        });

        foreach ($offlineUsers as $user) {
            UserOffline::dispatch($user);
        }

        $this->info('Cleaned offline users: '.count($offlineUsers));
        $this->info('Online users: '.count($onlineUsers));
    }
}
