<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Products | Product Desk</title>
    <style>
        :root { --ink: #15241d; --muted: #66756c; --paper: #f5f2e9; --panel: #fffdf7; --accent: #d85b32; --line: #ded8c9; } * { box-sizing: border-box; } body { margin: 0; min-height: 100vh; padding: 42px 20px; background: linear-gradient(135deg, #e7eee0 0 24%, var(--paper) 24%); color: var(--ink); font: 16px Arial, sans-serif; } main { width: min(1100px, 100%); margin: auto; } header { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 28px; } h1 { margin: 0; font: 3rem Georgia, serif; } p { color: var(--muted); } a, button { color: inherit; } .actions { display: flex; gap: 10px; align-items: center; } .button { display: inline-block; padding: 11px 15px; background: var(--accent); color: #fff; text-decoration: none; font-weight: 700; border: 0; cursor: pointer; } .logout { background: transparent; color: var(--muted); border: 1px solid var(--line); } .table-wrap { overflow-x: auto; background: var(--panel); border: 1px solid var(--line); } table { width: 100%; min-width: 760px; border-collapse: collapse; } th, td { padding: 15px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; } th { color: var(--muted); font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; } tr:last-child td { border-bottom: 0; } .number { text-align: right; } .row-actions { white-space: nowrap; } .row-actions a { color: var(--accent); font-weight: 700; margin-right: 12px; } .inline { display: inline; } .empty { padding: 42px; text-align: center; color: var(--muted); }
    </style>
</head>
<body><main>
    <header><div><h1>Products</h1><p>Inventory records stored in Aiven MySQL.</p></div><div class="actions"><a class="button" href="/products/create">Add product</a><form class="inline" method="post" action="/logout"><button class="button logout" type="submit">Sign out</button></form></div></header>
    <div class="table-wrap"><table><thead><tr><th>Product</th><th>Description</th><th class="number">Price</th><th class="number">Quantity</th><th>Created</th><th></th></tr></thead><tbody>
    <?php if (empty($products)): ?><tr><td class="empty" colspan="6">No products yet.</td></tr><?php else: foreach ($products as $product): ?><tr>
        <td><strong><?= html_escape($product['product_name'] ?? '') ?></strong></td><td><?= nl2br(html_escape($product['description'] ?? '')) ?></td><td class="number">$<?= number_format((float)($product['price'] ?? 0), 2) ?></td><td class="number"><?= html_escape($product['quantity'] ?? 0) ?></td><td><?= html_escape($product['created_at'] ?? '') ?></td><td class="row-actions"><a href="/products/edit/<?= (int)$product['id'] ?>">Edit</a><form class="inline" method="post" action="/products/delete/<?= (int)$product['id'] ?>"><button type="submit">Delete</button></form></td>
    </tr><?php endforeach; endif; ?></tbody></table></div>
</main></body></html>
