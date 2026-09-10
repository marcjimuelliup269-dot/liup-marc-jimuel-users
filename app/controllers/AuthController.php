<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function login()
    {
        $session = $this->call->library('session');
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $configured_username = getenv('AUTH_USERNAME') ?: '';
            $configured_hash = getenv('AUTH_PASSWORD_HASH') ?: '';
            $configured_password = getenv('AUTH_PASSWORD') ?: '';
            $password_valid = $configured_hash !== ''
                ? password_verify($password, $configured_hash)
                : ($configured_password !== '' && hash_equals($configured_password, $password));

            if ($username !== '' && hash_equals($configured_username, $username) && $password_valid) {
                $session->regenerate_on_login(true);
                $session->set_userdata('authenticated', true);
                $session->set_userdata('auth_username', $username);
                redirect('/products');
            }

            $error = 'Invalid username or password.';
        }

        $this->call->view('login', ['error' => $error]);
    }

    public function logout()
    {
        $session = $this->call->library('session');
        $session->sess_destroy();
        redirect('/login');
    }
}
