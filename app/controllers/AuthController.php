<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    private $users;

    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->users = $this->call->model('UsersModel');
    }

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

            $user = $this->users->find_by('username', $username);
            if ($user && !empty($user['is_active']) && password_verify($password, $user['password'])) {
                $password_valid = true;
                $configured_username = $username;
            }

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

    public function register()
    {
        $error = null;
        $old = ['username' => '', 'email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $password_confirmation = (string)($_POST['password_confirmation'] ?? '');
            $old = ['username' => $username, 'email' => $email];

            if ($username === '' || strlen($username) > 100) {
                $error = 'Username is required and must be 100 characters or fewer.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $error = 'Enter a valid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $password_confirmation) {
                $error = 'Passwords do not match.';
            } elseif ($this->users->find_by('username', $username)) {
                $error = 'That username is already registered.';
            } elseif ($this->users->find_by('email', $email)) {
                $error = 'That email is already registered.';
            } else {
                $this->users->insert([
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'user',
                    'is_active' => 1,
                ]);
                redirect('/login?registered=1');
            }
        }

        $this->call->view('register', ['error' => $error, 'old' => $old]);
    }

    public function logout()
    {
        $session = $this->call->library('session');
        $session->sess_destroy();
        redirect('/login');
    }
}
