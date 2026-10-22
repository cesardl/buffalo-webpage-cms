<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit\Http;

use Buffalo\Http\Access;
use Buffalo\Http\ArraySession;
use Buffalo\Http\Request;
use Buffalo\Http\Route;
use Buffalo\Http\Router;
use Buffalo\Tests\Support\StubController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
#[CoversClass(Route::class)]
#[CoversClass(Access::class)]
final class RouterTest extends TestCase
{
    private ArraySession $session;
    private StubController $controller;

    protected function setUp(): void
    {
        $this->session = new ArraySession();
        $this->controller = new StubController();
    }

    public function testAnUnknownPathIsNotFound(): void
    {
        $response = $this->router()->dispatch(new Request('GET', '/nope'));

        self::assertSame(404, $response->status);
        self::assertNull($this->controller->received);
    }

    public function testAPathThatOnlyMatchesAnotherMethodIsNotFound(): void
    {
        $response = $this->router()->dispatch(new Request('POST', '/publica'));

        self::assertSame(404, $response->status);
    }

    public function testAPublicRouteRunsWithoutASession(): void
    {
        $response = $this->router()->dispatch(new Request('GET', '/publica'));

        self::assertSame(200, $response->status);
        self::assertSame('handled', $response->body);
    }

    public function testAnAdminPageRedirectsToLoginWhenSignedOut(): void
    {
        $response = $this->router()->dispatch(new Request('GET', '/privada'));

        self::assertSame(302, $response->status);
        self::assertSame('/entrar?error=2', $response->headers['Location']);
        self::assertNull($this->controller->received, 'The controller must not run.');
    }

    public function testAnAdminPageRunsOnceSignedIn(): void
    {
        $this->session->logIn(1, 'root');

        $response = $this->router()->dispatch(new Request('GET', '/privada'));

        self::assertSame(200, $response->status);
    }

    public function testAnXhrEndpointIsForbiddenRatherThanRedirected(): void
    {
        // A redirect would hand the caller a login page, which the page would
        // then inject into itself as though it were data.
        $response = $this->router()->dispatch(new Request('GET', '/api'));

        self::assertSame(403, $response->status);
        self::assertArrayNotHasKey('Location', $response->headers);
    }

    public function testAPostWithoutATokenIsRejected(): void
    {
        $this->session->logIn(1, 'root');

        $response = $this->router()->dispatch(new Request('POST', '/guardar'));

        self::assertSame(403, $response->status);
        self::assertNull($this->controller->received);
    }

    public function testAPostWithTheWrongTokenIsRejected(): void
    {
        $this->session->logIn(1, 'root');

        $response = $this->router()->dispatch(
            new Request('POST', '/guardar', post: ['_token' => 'nope']),
        );

        self::assertSame(403, $response->status);
    }

    public function testAPostWithTheRightTokenRuns(): void
    {
        $this->session->logIn(1, 'root');

        $response = $this->router()->dispatch(
            new Request('POST', '/guardar', post: ['_token' => $this->session->csrfToken()]),
        );

        self::assertSame(200, $response->status);
    }

    public function testEvenAPublicPostNeedsAToken(): void
    {
        // The sign-in form posts to a public route; exempting routes one at a
        // time is how the session checks came to be missing before.
        $response = $this->router()->dispatch(new Request('POST', '/entrar'));

        self::assertSame(403, $response->status);
    }

    public function testTheSessionIsCheckedBeforeTheToken(): void
    {
        $response = $this->router()->dispatch(new Request('POST', '/guardar'));

        self::assertSame(302, $response->status, 'Signed out should say so, not blame the token.');
    }

    private function router(): Router
    {
        $routes = [
            new Route('GET', '/publica', Access::Public, StubController::class),
            new Route('POST', '/entrar', Access::Public, StubController::class),
            new Route('GET', '/privada', Access::Admin, StubController::class),
            new Route('POST', '/guardar', Access::Admin, StubController::class),
            new Route('GET', '/api', Access::AdminApi, StubController::class),
        ];

        return new Router(
            routes: $routes,
            session: $this->session,
            resolve: fn (string $class) => $this->controller,
            loginPath: '/entrar',
        );
    }
}
