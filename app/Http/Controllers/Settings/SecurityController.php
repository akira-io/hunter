<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Profile\DeleteAccountAction;
use App\Actions\Settings\ActiveSessionsAction;
use App\Actions\Settings\ConnectedAccountsAction;
use App\Actions\Settings\DisconnectOAuthAccountAction;
use App\Actions\Settings\LogoutOtherDevicesAction;
use App\Actions\Settings\RevokeSessionAction;
use App\Http\Requests\Settings\DeleteAccountRequest;
use App\Http\Requests\Settings\RevokeSessionRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('settings/security')]
final readonly class SecurityController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private RevokeSessionAction $revokeSessionAction,
        private LogoutOtherDevicesAction $logoutOtherDevicesAction,
        private DisconnectOAuthAccountAction $disconnectOAuthAccountAction,
        private ActiveSessionsAction $activeSessionsAction,
        private ConnectedAccountsAction $connectedAccountsAction,
        private DeleteAccountAction $deleteAccountAction,
    ) {}

    /**
     * Show the user's security settings page.
     */
    #[Get('/', name: 'security.index')]
    public function index(Request $request): Response
    {
        $user = type($request->user())->as(User::class);

        return Inertia::render('settings/security', [
            'activeSessions' => $this->activeSessionsAction->handle($user),
            'connectedAccounts' => $this->connectedAccountsAction->handle($user),
            'hasPassword' => ! empty($user->password),
        ]);
    }

    /**
     * Revoke a specific session.
     */
    #[Delete('/sessions/{sessionId}', name: 'security.revoke-session')]
    public function revokeSession(RevokeSessionRequest $request, int $sessionId): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $success = $this->revokeSessionAction->handle($user, $sessionId);

        if ($success) {
            return back()->with('success', 'Sessão revogada com sucesso.');
        }

        return back()->with('error', 'Não foi possível revogar a sessão.');
    }

    /**
     * Logout from all other devices.
     */
    #[Post('/logout-other-devices', name: 'security.logout-other-devices')]
    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);
        $currentIp = type($request->ip())->asString();

        $count = $this->logoutOtherDevicesAction->handle($user, $currentIp);

        if ($count > 0) {
            return back()->with('success', "Você saiu de {$count} ".($count === 1 ? 'dispositivo' : 'dispositivos').' com sucesso.');
        }

        return back()->with('info', 'Não há outras sessões ativas.');
    }

    /**
     * Disconnect OAuth account.
     */
    #[Delete('/accounts/{provider}', name: 'security.disconnect-account')]
    public function disconnectAccount(Request $request, string $provider): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $success = $this->disconnectOAuthAccountAction->handle($user, $provider);

        if (! $success) {
            if (empty($user->password)) {
                return back()->with('error', 'Você precisa definir uma senha antes de desconectar esta conta.');
            }

            return back()->with('error', 'Provedor inválido ou conta não conectada.');
        }

        return back()->with('success', 'Conta do '.ucfirst($provider).' desconectada com sucesso.');
    }

    /**
     * Delete the user's account.
     */
    #[Delete('/account', name: 'security.delete-account')]
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->deleteAccountAction->handle(user: $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
