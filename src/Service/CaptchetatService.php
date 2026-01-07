<?php

namespace OpenWebAddict\CaptchetatBundle\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchetatService implements CaptchetatServiceInterface
{

    private const API_URL='https://api.piste.gouv.fr';
    private const OAUTH_URL='https://oauth.piste.gouv.fr';
    private const SANDBOX_API_URL='https://sandbox-api.piste.gouv.fr';
    private const SANDBOX_OAUTH_URL='https://sandbox-oauth.piste.gouv.fr';
    private const API_PATH='/piste/captchetat/v2';

    public function __construct(
        private bool $sandbox,
        private string $clientId,
        private string $clientSecret,
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache
    ){}


    protected function getApiUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_API_URL : self::API_URL;
    }

    protected function getOAuthUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_OAUTH_URL : self::OAUTH_URL;
    }

    public function healthCheck(string $token): mixed
    {
        if (empty($token)) {
            return false;
        }

        $url = $this->getApiUrl() . self::API_PATH . '/healthcheck';
        $headers = [
            'Authorization: Bearer ' . $token
        ];

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => $headers
            ]);

            $contents = $response->getContent();

            if (empty($contents)) {
                return false;
            } else {
                return json_decode($contents, true);
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    public function fetchApiToken(): string
    {
        $oAuthUrl = $this->getOAuthUrl();
        if (empty($oAuthUrl) || empty($this->clientId) || empty($this->clientSecret)) {
            $this->logger->warning('Invalid or missing Captchetat configuration');

            return '';
        }

        $url = $oAuthUrl . '/api/oauth/token';
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'piste.captchetat',
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => $data
            ]);

            $contents = $response->getContent();
            $tokenData = json_decode($contents, true);

            if (empty($tokenData['access_token']) || empty($tokenData['expires_in'])) {
                $this->logger->error('Invalid token response from OAuth server.');
                return '';
            }

            $token = $tokenData['access_token'];
            $expiresIn = (int) $tokenData['expires_in'];

            $cacheTtl = (int) ($expiresIn / 2);

            $this->cache->save(
                $this->cache->getItem('captchetat.access_token')->set($token)->expiresAfter($cacheTtl)
            );

            return $token;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return '';
        }
    }

    public function getApiToken(): string
    {
        $cacheItem = $this->cache->getItem('captchetat.access_token');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        return $this->fetchApiToken();
    }

    public function isServiceUp(string $token): bool
    {
        $statusInfos = $this->healthCheck($token);
        if ($statusInfos && isset($statusInfos['status'])) {
            return $statusInfos['status'] === 'UP';
        }

        return false;
    }

    public function getCaptcha(string $captchaObjectType = 'image', string $captchaType = 'numerique6_7CaptchaFR', ?string $captchaId = null): string
    {
        $availableTypes = [
            'image',
            'sound',
        ];

        if (!in_array($captchaObjectType, $availableTypes)) {
            return '';
        }

        $token = $this->getApiToken();
        if (empty($token)) {
            return '';
        }

        $url = $this->getApiUrl() . '/piste/captchetat/v2/simple-captcha-endpoint';
        $queryParams = [
            'get' => $captchaObjectType,
            'c' => $captchaType,
        ];

        $option = [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            'verify_host' => false,
            'verify_peer' => false,
        ];

        if ($captchaId) {
            $queryParams['t'] = $captchaId;
        }

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => $queryParams,
                'options' => $option,
            ]);

            return $response->getContent();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function validateCaptcha(string $captchaUuid, string $userEnteredCode): bool
    {
        $token = $this->getApiToken();
        if (empty($token)) {
            return false;
        }

        $url = $this->getApiUrl() . '/piste/captchetat/v2/valider-captcha';
        $data = [
            'uuid' => $captchaUuid,
            'code' => $userEnteredCode,
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            'verify_host' => false,
            'verify_peer' => false,
        ];
        
        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => $data,
                'options' => $options,
            ]);

            $contents = $response->getContent();
            
            return "true" === $contents;

        } catch (\Exception $e) {
            return false;
        }
    }

}
