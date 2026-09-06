<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | LavaLust</title>
    <style>
        :root { --bg: #101114; --surface: #181a1f; --border: #30323a; --text: #f4f4f5; --muted: #b4b7c0; --accent: #dd4814; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; padding: 48px 20px; background: var(--bg); color: var(--text); font: 16px system-ui, sans-serif; }
        main { width: min(1100px, 100%); margin: 0 auto; }
        h1 { margin: 0 0 8px; }
        p { margin: 0 0 24px; color: var(--muted); }
        .table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: 10px; background: var(--surface); }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { padding: 14px 16px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: rgba(221, 72, 20, 0.14); color: #ffb39a; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.04em; }
        tr:last-child td { border-bottom: 0; }
        .empty { color: var(--muted); text-align: center; }
    </style>
</head>
<body>
    <main>
        <h1>Users</h1>
        <p>Records retrieved from the <strong>users</strong> table.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Username</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td class="empty" colspan="5">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= html_escape($user['id'] ?? '') ?></td>
                                <td><?= html_escape($user['firstname'] ?? '') ?></td>
                                <td><?= html_escape($user['lastname'] ?? '') ?></td>
                                <td><?= html_escape($user['email'] ?? '') ?></td>
                                <td><?= html_escape($user['username'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>