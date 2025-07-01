<?php

namespace OpenWebAddict\CaptchetatBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchetatService implements CaptchetatServiceInterface
{

    private const API_URL='https://api.piste.gouv.fr';
    private const OAUTH_URL='https://oauth.piste.gouv.fr';
    private const SANDBOX_API_URL='https://sandbox-api.piste.gouv.fr';
    private const SANDBOX_OAUTH_URL='https://sandbox-oauth.piste.gouv.fr';
    private const API_PATH='/piste/captchetat/v2';

    public function __construct(
        private string $sandbox,
        private string $client_id,
        private string $client_secret,
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient
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
            $this->logger->error('No CaptchEtat API token provided.');

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

}
