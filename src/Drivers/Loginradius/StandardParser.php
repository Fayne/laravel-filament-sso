<?php

namespace Fayne\Sso\Drivers\Loginradius;

use Fayne\Sso\Parsers\AbstractStandardParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Two\User;
use Symfony\Component\HttpFoundation\Response;

class StandardParser extends AbstractStandardParser
{
    public function getUser(): ?User
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');

        if ($email && $password) {
            return $this->getUserByCredentials($email, $password);
        }

        return null;
    }

    protected function getUserByCredentials(string $email, string $password): ?User
    {
        $apiKey = config('filament-sso.loginradius.standard.api_key');

        $response = Http::post('https://api.loginradius.com/identity/v2/auth/login?apikey='.$apiKey, [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Profile info is usually in 'Profile' key, or at root
            return $this->mapUser($data['Profile'] ?? $data);
        }

        return null;
    }

    protected function mapUser(array $profile): User
    {
        $id = $profile['Uid'] ?? $profile['ID'] ?? null;

        $email = null;
        if (isset($profile['Email']) && is_array($profile['Email'])) {
            $email = $profile['Email'][0]['Value'] ?? null;
        } elseif (is_string($profile['Email'] ?? null)) {
            $email = $profile['Email'];
        }

        $name = $profile['FullName'] ?? trim(($profile['FirstName'] ?? '').' '.($profile['LastName'] ?? ''));
        if (empty($name)) {
            $name = $profile['UserName'] ?? $email;
        }

        return (new User)->setRaw($profile)->map([
            'id' => $id,
            'email' => $email,
            'name' => trim($name),
            'nickname' => $profile['NickName'] ?? $profile['nickname'] ?? null,
            'avatar' => $profile['ThumbnailImageUrl'] ?? null,
        ]);
    }

    public static function redirect(): Response
    {
        $appName = config('filament-sso.loginradius.standard.app_name');
        $returnUrl = url()->current();

        $url = sprintf(
            'https://%s.hub.loginradius.com/auth.aspx?action=login&return_url=%s',
            $appName,
            urlencode($returnUrl)
        );

        return new RedirectResponse($url);
    }
}
