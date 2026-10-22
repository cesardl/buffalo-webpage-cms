<?php

/*
 * Session guards for the administrator.
 *
 * Every admin entry point calls one of these before doing anything else.
 * Until this existed, only the form*.php pages checked the session: the
 * action*.php controllers and both Vehiculo*.php endpoints could be driven
 * by anyone who could reach the port.
 *
 * The form*.php pages keep their own inline check for now. Phase 4 replaces
 * both mechanisms with a single guard in the router.
 */

function admin_is_authenticated()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return isset($_SESSION['user']);
}

/**
 * For endpoints that render a page: behaves like the existing form*.php
 * pages and sends the visitor back to the login screen.
 */
function require_admin()
{
    if (!admin_is_authenticated()) {
        header('Location: index.php?error=2');
        exit;
    }
}

/**
 * For endpoints the browser calls with $.post, where a redirect to an HTML
 * login page would just be injected into the page as if it were data.
 */
function require_admin_ajax()
{
    if (!admin_is_authenticated()) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
}
