<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Single sign-on') }}</title>
</head>
<body>
    <main>
        <h1>{{ __('Single sign-on') }}</h1>
        @if ($errors->has('sso'))
            <p role="alert">{{ $errors->first('sso') }}</p>
        @endif
        @forelse (array_filter(config('filament-sso.providers', []), fn (array $provider): bool => (bool) ($provider['enabled'] ?? false)) as $driver => $provider)
            <p><a href="{{ route('filament-sso.redirect', $driver) }}">{{ __('Continue with :provider', ['provider' => ucfirst($driver)]) }}</a></p>
        @empty
            <p>{{ __('No single sign-on providers are enabled.') }}</p>
        @endforelse
    </main>
</body>
</html>
