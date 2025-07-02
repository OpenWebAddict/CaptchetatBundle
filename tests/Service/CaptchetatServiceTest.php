<?php

namespace OpenWebAddict\CaptchetatBundle\Tests\Service;

use OpenWebAddict\CaptchetatBundle\Service\CaptchetatService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CaptchetatServiceTest extends TestCase
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
    }

    public function testGetApiUrlSandBox(): void
    {
        $captchetatService = $this->getServiceSandBox();

        $reflection = new \ReflectionClass($captchetatService);
        $method = $reflection->getMethod('getApiUrl');
        $method->setAccessible(true);

        $url = $method->invoke($captchetatService);

        $this->assertEquals('https://sandbox-api.piste.gouv.fr', $url);
    }

    public function testGetApiUrlProduction(): void
    {
        $captchetatService = $this->getServiceProduction();

        $reflection = new \ReflectionClass($captchetatService);
        $method = $reflection->getMethod('getApiUrl');
        $method->setAccessible(true);

        $url = $method->invoke($captchetatService);

        $this->assertEquals('https://api.piste.gouv.fr', $url);
    }

    public function testGetOAuthUrlSandBox(): void
    {
        $captchetatService = $this->getServiceSandBox();

        $reflection = new \ReflectionClass($captchetatService);
        $method = $reflection->getMethod('getOAuthUrl');
        $method->setAccessible(true);

        $url = $method->invoke($captchetatService);

        $this->assertEquals('https://sandbox-oauth.piste.gouv.fr', $url);
    }

    public function testGetOAuthUrlProduction(): void
    {
        $captchetatService = $this->getServiceProduction();

        $reflection = new \ReflectionClass($captchetatService);
        $method = $reflection->getMethod('getOAuthUrl');
        $method->setAccessible(true);

        $url = $method->invoke($captchetatService);

        $this->assertEquals('https://oauth.piste.gouv.fr', $url);
    }

    public function testHealthCheckEmptyToken(): void
    {
        $captchetatService = $this->getServiceProduction();

        $this->assertFalse($captchetatService->healthCheck(''));
    }

    public function testHealthCheckException(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('error'));

        $this->logger->expects($this->once())->method('error');

        $captchetatService = $this->getServiceProduction();

        $this->assertFalse($captchetatService->healthCheck('token'));
    }

    public function testHealthCheckEmptyResult(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getContent')->willReturn('');
    
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $captchetatService = $this->getServiceProduction();

        $this->assertFalse($captchetatService->healthCheck('token'));
    }

    public function testHealthCheck(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getContent')->willReturn('{"status": "ok"}');
    
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $captchetatService = $this->getServiceProduction();

        $data = $captchetatService->healthCheck('token');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
    }

    public function testIsServiceUp(): void
    {
        $captchetatService = $this->getMockBuilder(CaptchetatService::class)
            ->onlyMethods(['healthCheck'])
            ->disableOriginalConstructor()
            ->getMock();

        $captchetatService->expects($this->once())
            ->method('healthCheck')
            ->willReturn(['status' => 'UP']);

        $this->assertTrue($captchetatService->isServiceUp('token'));
    }

    public function testIsServiceUpDown(): void
    {
        $captchetatService = $this->getMockBuilder(CaptchetatService::class)
            ->onlyMethods(['healthCheck'])
            ->disableOriginalConstructor()
            ->getMock();

        $captchetatService->expects($this->once())
            ->method('healthCheck')
            ->willReturn(null);

        $this->assertFalse($captchetatService->isServiceUp('token'));
    }

    public function testGetApiToken(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn('token');
        
        
        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('captchetat.access_token')
            ->willReturn($cacheItem);

        
        $captchetatService = $this->getServiceSandBox();

        $this->assertEquals('token', $captchetatService->getApiToken());
    }

    public function testGetApiTokenNoCache(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('captchetat.access_token')
            ->willReturn($cacheItem);
        
        $captchetatService = $this->getMockBuilder(CaptchetatService::class)
            ->onlyMethods(['fetchApiToken'])
            ->enableOriginalConstructor()
            ->setConstructorArgs([true, 'clientId', 'clientSecret', $this->logger, $this->httpClient, $this->cache])
            ->getMock();
        
        $captchetatService->expects($this->once())
            ->method('fetchApiToken')
            ->willReturn('token');
        
        $this->assertEquals('token', $captchetatService->getApiToken());
    }

    private function getServiceSandBox(
        bool $sandBox = true, 
        string $clientId = 'clientId', 
        string $clientSecret = 'clientSecret'
    ): CaptchetatService
    {
        return new CaptchetatService(
            $sandBox,
            $clientId,
            $clientSecret,
            $this->logger,
            $this->httpClient,
            $this->cache
        );
    }

    private function getServiceProduction(): CaptchetatService
    {
        return new CaptchetatService(
            false,
            'clientId',
            'clientSecret',
            $this->logger,
            $this->httpClient,
            $this->cache
        );
    }

    
}
