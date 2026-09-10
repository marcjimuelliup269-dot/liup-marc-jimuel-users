<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Product Desk</title>
    <style>
        :root { --ink: #15241d; --muted: #66756c; --paper: #f5f2e9; --panel: #fffdf7; --accent: #d85b32; --line: #ded8c9; }
        * { box-sizing: border-box; } body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: radial-gradient(circle at 15% 10%, #e3eadb, transparent 35%), var(--paper); color: var(--ink); font: 16px Georgia, serif; }
        main { width: min(420px, 100%); padding: 42px; background: var(--panel); border: 1px solid var(--line); box-shadow: 12px 12px 0 #dfe5d8; } h1 { margin: 0 0 8px; font-size: 2.4rem; } p { color: var(--muted); line-height: 1.5; } label { display: block; margin: 20px 0 7px; font-family: Arial, sans-serif; font-size: .82rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; } input { width: 100%; padding: 13px; border: 1px solid var(--line); background: #fff; font: inherit; } button { width: 100%; margin-top: 24px; padding: 14px; border: 0; background: var(--accent); color: #fff; font: 700 1rem Arial, sans-serif; cursor: pointer; } .error { padding: 10px 12px; border-left: 4px solid var(--accent); background: #fff0e9; color: #8b321b; }
    </style>
</head>
<body><main>
    <h1>Product Desk</h1><p>Sign in to manage the product inventory.</p>
    <?php if (!empty($error)): ?><p class="error"><?= html_escape($error) ?></p><?php endif; ?>
    <form method="post" action="/login">
        <label for="username">Username</label><input id="username" name="username" required autocomplete="username">
        <label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Sign in</button>
    </form>
</main></body></html>
