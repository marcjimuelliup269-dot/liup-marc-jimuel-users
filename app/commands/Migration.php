<?php

class Migration
{
    public static $command = 'migration';
    public static $description = 'Run database migrations';
    public static $arguments = [
        '[action]' => 'Action: run, create-migration, rollback, rollback-all, refresh, status',
        '[name]' => 'Migration class name for create-migration',
    ];

    protected static $route_map = [
        'run' => 'migrate',
        'create-migration' => 'create-migration',
        'rollback' => 'rollback',
        'rollback-all' => 'rollback-all',
        'refresh' => 'refresh',
        'status' => 'status',
    ];

    public function handle($action = null, array $flags = [], $name = null)
    {
        $action = $action ?? 'run';
        if (!isset(static::$route_map[$action])) {
            echo danger("Unknown migration action: \"{$action}\"") . PHP_EOL;
            echo 'Available actions: ' . implode(', ', array_keys(static::$route_map)) . PHP_EOL;
            exit(1);
        }

        if ($action === 'create-migration') {
            if (!$name || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $name)) {
                echo danger('A valid migration name is required.') . PHP_EOL;
                echo 'Example: php lava migration create-migration create_users_table' . PHP_EOL;
                exit(1);
            }
            $route = 'create-migration/' . $name;
        } else {
            $route = static::$route_map[$action];
        }

        $index = PUBLIC_DIR . 'index.php';
        if (!file_exists($index)) {
            echo danger("index.php not found at: {$index}") . PHP_EOL;
            exit(1);
        }

        passthru(sprintf('php %s %s', escapeshellarg($index), escapeshellarg($route)), $exit_code);
        if ($exit_code !== 0) {
            exit($exit_code);
        }
    }
}