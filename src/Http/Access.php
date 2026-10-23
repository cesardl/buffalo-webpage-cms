<?php

declare(strict_types=1);

namespace Buffalo\Http;

/**
 * Who may reach a route.
 *
 * Route takes this as a required argument with no default, so a new route
 * cannot be added without someone deciding. The legacy hole was exactly this
 * decision being made by omission: form*.php checked the session, the
 * action*.php controllers simply never did.
 */
enum Access
{
    /** Anyone. */
    case Public;

    /** Signed in, or sent to the login page -- what a browser should get. */
    case Admin;

    /**
     * Signed in, or 403 -- for the endpoints the page calls with XHR, where
     * redirecting to an HTML login form would just be injected into the page
     * as though it were data.
     */
    case AdminApi;

    public function requiresSession(): bool
    {
        return $this !== self::Public;
    }
}
