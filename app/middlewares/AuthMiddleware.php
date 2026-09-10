<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthMiddleware
{
    public function handle($next)
    {
        $session = load_class('session', 'libraries');

        if ($session->userdata('authenticated') !== true) {
            redirect('/login');
        }

        return $next();
    }
}
