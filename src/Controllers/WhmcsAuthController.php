<?php

namespace DelzyionCloud\FlarumWhmcs\Controllers;

use Exception;
use Flarum\Forum\Auth\Registration;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Http\UrlGenerator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class WhmcsAuthController implements RequestHandlerInterface
{
    protected ResponseFactory $response;
    protected SettingsRepositoryInterface $settings;
    protected UrlGenerator $url;

    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->response = $response;
        $this->settings = $settings;
        $this->url = $url;
    }

    public function handle(Request $request): JsonResponse | RedirectResponse | ResponseInterface
    {
        $params = $request->getQueryParams();
        $session = $request->getAttribute('session');
        $error = Arr::get($params, 'error');
        $code = Arr::get($params, 'code');
        $state = Arr::get($params, 'state') ?: bin2hex(random_bytes(16));

        if ($error) {
            return new JsonResponse(
                [
                    'error' => $error,
                    'error_description' => Arr::get($params, 'error_description'),
                ],
                400
            );
        }

        if (!$code) {
           $session->put('whmcs.state', "security_token={$state}");
           return new RedirectResponse("{$this->settings->get('delzyion-flarum-whmcs.whmcs_url')}/oauth/authorize.php?client_id={$this->settings->get('delzyion-flarum-whmcs.client_id')}&response_type=code&scope=openid%20profile%20email&redirect_uri={$this->url->to('forum')->route('auth.whmcs')}&state=security_token%3D{$state}");
        }

        if (!$state || ($state !== $session->get('whmcs.state')) && !$session->get('whmcs.logged_in')) {
           $session->forget('whmcs.state');
           return new JsonResponse(
               [
                   'error' => 'invalid_state',
                   'error_description' => 'The state parameter does not match the stored value.'
               ],
                400
           );
        }

        $session->forget('whmcs.state');

        try {
            $client = new Client();
            $token = json_decode(
                $client
                    ->post("{$this->settings->get('delzyion-flarum-whmcs.whmcs_url')}/oauth/token.php", [
                        'form_params' => [
                            'code' => $code,
                            'client_id' => $this->settings->get('delzyion-flarum-whmcs.client_id'),
                            'client_secret' => $this->settings->get('delzyion-flarum-whmcs.client_secret'),
                            'redirect_uri' => $this->url->to('forum')->route('auth.whmcs'),
                            'grant_type' => 'authorization_code'
                        ]
                    ])
                    ->getBody()
                    ->getContents()
            )->access_token;

            $user = json_decode(
                $client
                    ->get("{$this->settings->get('delzyion-flarum-whmcs.whmcs_url')}/oauth/userinfo.php?access_token={$token}")
                    ->getBody()
                    ->getContents()
            );

            return $this->response->make(
                'whmcs',
                $user->email,
                function (Registration $registration) use ($user) {
                    $registration
                        ->provideTrustedEmail($user->email)
                        ->setPayload(json_decode(json_encode($user), true));
                }
            );
        } catch (Exception) {
            return new RedirectResponse(
                $this->url
                    ->to('forum')
                    ->route('auth.whmcs')
            );
        }
    }
}
