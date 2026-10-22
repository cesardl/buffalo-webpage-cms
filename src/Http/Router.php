<?php

declare(strict_types=1);

namespace Buffalo\Http;

use Closure;

/**
 * Matches a request to a route and enforces the two things every admin
 * endpoint needs, in one place.
 *
 * The legacy app enforced them per file, which meant each new endpoint had to
 * remember: form*.php checked $_SESSION, the action*.php controllers never
 * did, and nothing pointed that out. Here a route cannot be added without
 * declaring its Access, and no controller can forget the check because no
 * controller performs it.
 */
final readonly class Router
{
    /**
     * @param list<Route> $routes
     * @param Closure(string): Controller $resolve
     */
    public function __construct(
        private array $routes,
        private Session $session,
        private Closure $resolve,
        private string $loginPath,
    ) {
    }

    public function dispatch(Request $request): Response
    {
        $route = $this->match($request);

        if ($route === null) {
            return Response::notFound();
        }

        if ($route->access->requiresSession() && $this->session->usuario() === null) {
            return $route->access === Access::AdminApi
                ? Response::forbidden()
                : Response::redirect($this->loginPath . '?error=2');
        }

        // Every POST, including the public login. Exempting routes one by one
        // is how the session checks ended up missing in the first place.
        if ($request->method === 'POST'
            && !$this->session->csrfTokenMatches($request->postString('_token'))) {
            return Response::forbidden();
        }

        return ($this->resolve)($route->handler)->handle($request);
    }

    private function match(Request $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }

        return null;
    }
}
