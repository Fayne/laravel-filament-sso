<?php

namespace Fayne\Sso\Http\Controllers;

use Fayne\Sso\SsoManager;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SsoController extends Controller
{
    public function redirect(string $driver, SsoManager $sso): Response
    {
        abort_unless(config("filament-sso.providers.{$driver}.enabled", false), 404);

        return $sso->getProvider($driver)->redirect();
    }

    public function callback(string $driver, SsoManager $sso): RedirectResponse
    {
        abort_unless(config("filament-sso.providers.{$driver}.enabled", false), 404);

        try {
            $identity = $sso->getProvider($driver)->user();
        } catch (\Throwable) {
            return redirect()->route('filament-sso.login')->withErrors(['sso' => 'Single sign-on failed.']);
        }

        $mapping = config("filament-sso.identity_mapping.{$driver}");
        if (! $identity || ! is_array($mapping) || ! isset($mapping['claim'], $mapping['column'])) {
            return redirect()->route('filament-sso.login')->withErrors(['sso' => 'No administrator identity mapping is configured.']);
        }

        $claim = $mapping['claim'];
        $value = match ($claim) {
            'id' => $identity->getId(),
            'email' => $identity->getEmail(),
            'name' => $identity->getName(),
            default => data_get($identity->getRaw(), $claim),
        };
        $column = $mapping['column'];
        if ((! is_string($value) && ! is_int($value)) || trim((string) $value) === '') {
            return redirect()->route('filament-sso.login')->withErrors(['sso' => 'The required identity claim is missing or invalid.']);
        }
        abort_unless(is_string($column) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column), 500);

        $provider = config('auth.guards.'.config('filament-sso.identity.guard', 'admin').'.provider');
        $model = config('filament-sso.identity.model') ?: config("auth.providers.{$provider}.model");
        abort_unless(is_string($model) && class_exists($model), 500);
        $admin = $model::query()->where($column, $value)->first();
        if (! $admin instanceof Authenticatable || (isset($admin->is_active) && ! $admin->is_active)) {
            return redirect()->route('filament-sso.login')->withErrors(['sso' => 'Administrator account is unavailable.']);
        }

        if ($admin instanceof FilamentUser) {
            $panel = Filament::getPanel(config('filament-sso.panel', 'admin'));
            if (! $panel || ! $admin->canAccessPanel($panel)) {
                return redirect()->route('filament-sso.login')->withErrors(['sso' => 'Administrator cannot access this panel.']);
            }
        }

        if ($admin instanceof HasAppAuthentication
            && filled($admin->getAppAuthenticationSecret())) {
            return redirect()->route('filament-sso.login')->withErrors(['sso' => 'Please use password and two-factor authentication to sign in.']);
        }

        Auth::guard(config('filament-sso.identity.guard', 'admin'))->login($admin);

        return redirect()->intended(config('filament-sso.redirect', '/admin'));
    }
}
