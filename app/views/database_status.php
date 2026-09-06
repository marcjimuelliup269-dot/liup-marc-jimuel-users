<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Status | LavaLust</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #101114; color: #f4f4f5; font: 16px system-ui, sans-serif; }
        main { width: min(560px, calc(100% - 40px)); padding: 32px; border: 1px solid #30323a; border-radius: 12px; background: #181a1f; }
        h1 { margin-top: 0; }
        .status { color: <?= $status === 'Connected' ? '#65d48b' : '#ff8d75' ?>; font-weight: 700; }
        p { color: #b4b7c0; line-height: 1.6; overflow-wrap: anywhere; }
    </style>
</head>
<body>
    <main>
        <h1>Database status</h1>
        <div class="status"><?= html_escape($status) ?></div>
        <p><?= html_escape($message) ?></p>
    </main>
</body>
</html>