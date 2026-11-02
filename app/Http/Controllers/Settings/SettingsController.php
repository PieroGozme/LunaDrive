<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDriveQuota;
use App\Models\ConnectedAccount;
use App\Models\OauthState;
use App\Models\ProviderConfig;
use App\Services\Google\GoogleClientFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $config = ProviderConfig::globalGoogle();

        return Inertia::render('Settings/Index', [
            'googleConfigured' => (bool) $config,
            'googleRedirectUri' => $config?->redirect_uri ?: url('/connected-accounts/google/callback'),
            'accounts' => $request->user()->connectedAccounts()->with('storageAccount')->orderBy('email')->get()->map(fn ($a) => [
                'id' => $a->id,
                'email' => $a->email,
                'display_name' => $a->display_name,
                'avatar_url' => $a->avatar_url,
                'status' => $a->status,
                'quota_bytes' => $a->storageAccount?->quota_bytes,
                'used_bytes' => $a->storageAccount?->used_bytes,
                'last_error' => $a->last_error,
            ]),
        ]);
    }

    public function saveGoogleConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'redirect_uri' => ['required', 'url', 'max:500'],
        ]);

        $existing = ProviderConfig::globalGoogle();
        $payload = [
            'user_id' => null,
            'provider' => 'google_drive',
            'redirect_uri' => $data['redirect_uri'],
            'scopes' => config('lunadrive.google_scopes'),
            'status' => 'active',
        ];

        if ($existing) {
            $existing->client_id = $data['client_id'];
            $existing->client_secret = $data['client_secret'];
            $existing->fill($payload);
            $existing->save();
        } else {
            $config = new ProviderConfig($payload);
            $config->client_id = $data['client_id'];
            $config->client_secret = $data['client_secret'];
            $config->save();
        }

        return back()->with('success', 'Google OAuth credentials saved.');
    }

    public function connectGoogle(Request $request, GoogleClientFactory $clients): RedirectResponse
    {
        $config = ProviderConfig::globalGoogle();
        abort_unless($config, 422, 'Configure Google OAuth credentials first.');

        $state = Str::random(40);
        OauthState::query()->create([
            'user_id' => $request->user()->id,
            'provider_config_id' => $config->id,
            'flow' => 'connect',
            'state_hash' => hash('sha256', $state),
            'expires_at' => now()->addMinutes(15),
        ]);

        $client = $clients->forConfig($config);
        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }

    public function googleCallback(Request $request, GoogleClientFactory $clients): RedirectResponse
    {
        $state = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');
        abort_unless($state && $code, 400);

        $oauthState = OauthState::query()
            ->where('state_hash', hash('sha256', $state))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $config = $oauthState->providerConfig;
        $client = $clients->forConfig($config);
        $token = $client->fetchAccessTokenWithAuthCode($code);
        abort_if(isset($token['error']), 400, $token['error_description'] ?? 'OAuth failed');
        $client->setAccessToken($token);

        $oauth2 = new \Google\Service\Oauth2($client);
        $profile = $oauth2->userinfo->get();

        if ($oauthState->flow === 'login') {
            $user = \App\Models\User::query()->firstOrCreate(
                ['email' => $profile->getEmail()],
                [
                    'name' => $profile->getName() ?: $profile->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ],
            );
            $user->ensureRoutingPolicy();
            $userId = $user->id;
        } else {
            $userId = $oauthState->user_id;
            abort_unless($userId, 400);
        }

        $account = ConnectedAccount::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'provider' => 'google_drive',
                'provider_account_id' => (string) $profile->getId(),
            ],
            [
                'provider_config_id' => $config->id,
                'email' => $profile->getEmail(),
                'display_name' => $profile->getName(),
                'avatar_url' => $profile->getPicture(),
                'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                'scopes' => $config->scopes,
                'status' => 'connected',
                'last_error' => null,
            ],
        );

        $account->access_token = $token['access_token'] ?? null;
        if (! empty($token['refresh_token'])) {
            $account->refresh_token = $token['refresh_token'];
        }
        $account->save();

        $clients->ensureAppFolder($account);
        SyncDriveQuota::dispatch($account);
        $oauthState->update(['used_at' => now(), 'user_id' => $userId]);

        if ($oauthState->flow === 'login') {
            Auth::loginUsingId($userId);

            return redirect()->route('files.index');
        }

        return redirect()->route('settings.index')->with('success', 'Google Drive connected.');
    }

    public function disconnectAccount(Request $request, ConnectedAccount $account): RedirectResponse
    {
        abort_unless($account->user_id === $request->user()->id, 403);
        $account->update([
            'status' => 'revoked',
            'access_token_encrypted' => null,
            'refresh_token_encrypted' => null,
        ]);

        return back()->with('success', 'Account disconnected.');
    }

    public function googleLogin(GoogleClientFactory $clients): RedirectResponse
    {
        $config = ProviderConfig::globalGoogle();
        abort_unless($config, 422, 'Configure Google OAuth credentials first.');

        $state = Str::random(40);
        OauthState::query()->create([
            'user_id' => null,
            'provider_config_id' => $config->id,
            'flow' => 'login',
            'state_hash' => hash('sha256', $state),
            'expires_at' => now()->addMinutes(15),
        ]);

        $client = $clients->forConfig($config);
        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }
}

