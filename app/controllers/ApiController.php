<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiController extends Controller
{
    private $api;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->call->helper('api');
        lava_instance()->config->load('api');
        handle_cors();
        $this->db = $this->call->database();
        $this->api = $this->call->library('api');
        header('Content-Type: application/json; charset=utf-8');
    }

    public function options()
    {
        http_response_code(204);
    }

    public function login()
    {
        $input = $this->request_data();
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        if ($username === '' || $password === '') {
            $this->api->respond_error('Username and password are required.', 422);
        }

        $statement = $this->db->raw(
            'SELECT id, username, email, password, role, is_active FROM users WHERE username = ? LIMIT 1',
            [$username]
        );
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$user || empty($user['is_active']) || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid username or password.', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id' => $user['id'],
            'role' => $user['role'],
        ]);
        unset($user['password'], $user['is_active']);
        $this->api->respond(['user' => $user, 'tokens' => $tokens]);
    }

    public function register()
    {
        $input = $this->request_data();
        $username = trim((string)($input['username'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $password_confirmation = (string)($input['password_confirmation'] ?? '');

        if ($username === '' || strlen($username) > 100) {
            $this->api->respond_error('Username is required and must be 100 characters or fewer.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            $this->api->respond_error('Enter a valid email address.', 422);
        }
        if (strlen($password) < 8) {
            $this->api->respond_error('Password must be at least 8 characters.', 422);
        }
        if ($password !== $password_confirmation) {
            $this->api->respond_error('Passwords do not match.', 422);
        }

        $existing = $this->db->raw(
            'SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$username, $email]
        )->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $field = strcasecmp($existing['username'], $username) === 0 ? 'username' : 'email';
            $this->api->respond_error("That {$field} is already registered.", 409);
        }

        $this->db->raw(
            'INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)',
            [$username, $email, password_hash($password, PASSWORD_DEFAULT), 'user', 1]
        );
        http_response_code(201);
        $this->api->respond(['message' => 'Account created. You can now sign in.']);
    }

    public function refresh()
    {
        $input = $this->request_data();
        $token = $input['refresh_token'] ?? '';
        if (!is_string($token) || $token === '') {
            $this->api->respond_error('Refresh token is required.', 422);
        }

        $this->api->refresh_access_token($token);
    }

    public function logout()
    {
        $input = $this->request_data();
        $token = $input['refresh_token'] ?? '';
        if (is_string($token) && $token !== '') {
            $this->api->revoke_refresh_token($token);
        }

        $this->api->respond(['message' => 'Signed out.']);
    }

    public function index()
    {
        $this->api->require_jwt();
        $statement = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at FROM products ORDER BY id DESC'
        );
        $this->api->respond(['data' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function store()
    {
        $this->api->require_jwt();
        $data = $this->validated_product($this->request_data());
        if (isset($data['error'])) {
            $this->api->respond_error($data['error'], 422);
        }

        $this->db->raw(
            'INSERT INTO products (product_name, description, price, quantity) VALUES (?, ?, ?, ?)',
            [$data['product_name'], $data['description'], $data['price'], $data['quantity']]
        );
        $id = $this->db->raw('SELECT LAST_INSERT_ID()')->fetchColumn();
        $product = $this->find_product($id);
        http_response_code(201);
        $this->api->respond(['data' => $product]);
    }

    public function update($id)
    {
        $this->api->require_jwt();
        $current = $this->find_product((int)$id);
        if (!$current) {
            $this->api->respond_error('Product not found.', 404);
        }

        $input = $this->request_data();
        $data = $this->validated_product(array_merge($current, array_intersect_key(
            $input,
            array_flip(['product_name', 'description', 'price', 'quantity'])
        )));
        if (isset($data['error'])) {
            $this->api->respond_error($data['error'], 422);
        }

        $this->db->raw(
            'UPDATE products SET product_name = ?, description = ?, price = ?, quantity = ? WHERE id = ?',
            [$data['product_name'], $data['description'], $data['price'], $data['quantity'], (int)$id]
        );
        $this->api->respond(['data' => $this->find_product((int)$id)]);
    }

    public function delete($id)
    {
        $this->api->require_jwt();
        $statement = $this->db->raw('DELETE FROM products WHERE id = ?', [(int)$id]);
        if ($statement->rowCount() === 0) {
            $this->api->respond_error('Product not found.', 404);
        }

        $this->api->respond(['message' => 'Product deleted.']);
    }

    private function request_data()
    {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($content_type, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data)) {
                $this->api->respond_error('Request body must be valid JSON.', 400);
            }
            return $data;
        }

        return $_POST;
    }

    private function validated_product(array $input)
    {
        $name = trim((string)($input['product_name'] ?? ''));
        $description = (string)($input['description'] ?? '');
        $price = $input['price'] ?? null;
        $quantity = $input['quantity'] ?? null;

        if ($name === '' || strlen($name) > 100) {
            return ['error' => 'Product name is required and must be 100 characters or fewer.'];
        }
        if (!is_numeric($price) || !is_finite((float)$price) || (float)$price < 0 || (float)$price > 99999999.99) {
            return ['error' => 'Price must be between 0 and 99999999.99.'];
        }
        if (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int)$quantity < 0 || (int)$quantity > 2147483647) {
            return ['error' => 'Quantity must be a non-negative whole number.'];
        }

        return [
            'product_name' => $name,
            'description' => $description,
            'price' => number_format((float)$price, 2, '.', ''),
            'quantity' => (int)$quantity,
        ];
    }

    private function find_product($id)
    {
        $statement = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at FROM products WHERE id = ? LIMIT 1',
            [(int)$id]
        );
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}