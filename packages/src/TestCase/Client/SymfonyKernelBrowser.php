<?php

declare(strict_types=1);

namespace Polsl\Packages\TestCase\Client;

use Polsl\Infrastructure\Symfony\Security\SymfonyUser;
use Polsl\Packages\Constants\Server;
use Polsl\Packages\HttpResponse\CookieInterface;
use Polsl\Packages\HttpResponse\ResponseInterface;
use Polsl\Packages\HttpResponse\SymfonyResponse;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;

/** @internal */
final readonly class SymfonyKernelBrowser implements KernelBrowserInterface
{
    public function __construct(private KernelBrowser $kernelBrowser) {}

    public function followRedirect(): ResponseInterface
    {
        $crawler = $this->kernelBrowser
            ->followRedirect()
        ;
        $response = $this->kernelBrowser
            ->getResponse()
        ;
        /** @var string $uri */
        $uri = $crawler->getUri();

        return new SymfonyResponse($response, $uri);
    }

    public function followRedirects(bool $follow = true): void
    {
        $this->kernelBrowser
            ->followRedirects($follow)
        ;
    }

    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        string $content = '',
        array $headers = [],
    ): ResponseInterface {
        $crawler = $this->kernelBrowser
            ->request(
                $method,
                $uri,
                $parameters,
                server: $this->mapHeaders($headers),
                content: $content,
            )
        ;

        $response = $this->kernelBrowser
            ->getResponse()
        ;
        /** @var string $uri */
        $uri = $crawler->getUri();

        return new SymfonyResponse($response, $uri);
    }

    public function addCookie(CookieInterface $cookie): void
    {
        $cookieJar = $this->kernelBrowser
            ->getCookieJar()
        ;

        $symfonyCookie = Cookie::fromString($cookie->toString());
        $cookieJar->set($symfonyCookie);
    }

    public function disableReboot(): void
    {
        $this->kernelBrowser
            ->disableReboot()
        ;
    }

    public function loginUser(SymfonyUser $user): void
    {
        $this->kernelBrowser
            ->loginUser($user)
        ;
    }

    /**
     * @param array<string,string> $headers
     *
     * @return array<string,string>
     */
    private function mapHeaders(array $headers): array
    {
        $mappedHeaders = [];
        foreach ($headers as $name => $value) {
            if (Server::REMOTE_ADDR === $name) {
                $mappedHeaders[$name] = $value;

                continue;
            }

            $loweredName = \strtolower($name);
            if (!\str_starts_with($loweredName, 'http_')) {
                $name = "HTTP_{$name}";
            }

            $mappedHeaders[$name] = $value;
        }

        return $mappedHeaders;
    }
}
