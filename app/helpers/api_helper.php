<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!function_exists('handle_cors')) {
    function handle_cors()
    {
        $configured_origin = (string) config_item('allow_origin');
        $request_origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($configured_origin === '*') {
            header('Access-Control-Allow-Origin: *');
        } elseif ($request_origin !== '' && hash_equals($configured_origin, $request_origin)) {
            header('Access-Control-Allow-Origin: ' . $configured_origin);
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}