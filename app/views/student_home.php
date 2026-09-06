<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | Marc Liup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700;800&family=Unbounded:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --lava: #dd4814;
            --lava-dim: #b83a10;
            --lava-glow: rgba(221,72,20,0.18);
            --bg: #0a0a0b;
            --bg2: #111113;
            --bg3: #18181b;
            --card: rgba(17,17,19,0.9);
            --border: rgba(255,255,255,0.08);
            --border-hot: rgba(221,72,20,0.35);
            --text: #f4f4f5;
            --text-muted: #a1a1aa;
            --mono: 'Fira Code', monospace;
            --sans: 'Unbounded', sans-serif;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px),
                radial-gradient(circle at top left, rgba(221,72,20,0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(221,72,20,0.12), transparent 25%);
            background-size: 42px 42px, 42px 42px, 100% 100%, 100% 100%;
        }

        .topbar {
            position: sticky;
            top: 0;
            backdrop-filter: blur(12px);
            background: rgba(10,10,11,0.75);
            border-bottom: 1px solid var(--border);
            z-index: 10;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 72px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.05rem;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
        }

        .brand-mark {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--lava);
            box-shadow: 0 0 20px var(--lava-glow);
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.8rem;
            padding: 9px 12px;
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--text);
            background: rgba(255,255,255,0.03);
        }

        .nav-links .primary {
            color: #fff;
            background: var(--lava);
            border: 1px solid var(--border-hot);
            box-shadow: 0 0 18px var(--lava-glow);
        }

        .hero {
            padding: 90px 0 64px;
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border: 1px solid var(--border-hot);
            background: rgba(221,72,20,0.08);
            color: #fbbf24;
            border-radius: 999px;
            font-family: var(--mono);
            font-size: 0.7rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .badge::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--lava);
            box-shadow: 0 0 12px var(--lava);
        }

        h1 {
            margin: 24px auto 18px;
            max-width: 720px;
            font-size: clamp(2.5rem, 5vw, 5rem);
            line-height: 1.02;
            letter-spacing: -0.05em;
        }

        .highlight {
            color: var(--lava);
        }

        .subtitle {
            max-width: 640px;
            margin: 0 auto;
            color: var(--text-muted);
            font-size: 1.04rem;
            line-height: 1.75;
        }

        .cta-row {
            margin-top: 28px;
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 14px 22px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: var(--lava);
            color: white;
            box-shadow: 0 0 18px var(--lava-glow);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.02);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .panel {
            max-width: 760px;
            margin: 28px auto 0;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
            padding: 28px;
        }

        .panel-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .mini-card {
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.02);
            border-radius: 12px;
            padding: 18px;
            text-align: left;
        }

        .mini-label {
            font-family: var(--mono);
            color: var(--text-muted);
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .mini-value {
            font-size: 1.15rem;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .panel-grid {
                grid-template-columns: 1fr;
            }

            .nav {
                flex-direction: column;
                padding: 16px 0;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            <a href="<?= site_url('student'); ?>" class="brand">
                <span class="brand-mark">L</span>
                <span>LavaLust Student</span>
            </a>
            <nav class="nav-links">
                <a href="<?= site_url('student'); ?>">Home</a>
                <a class="primary" href="<?= site_url('student/profile'); ?>">View Profile</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <div class="badge">Student Portal</div>
            <h1>Marc Jimuel <span class="highlight">Gutierrez</span> Liup</h1>
            <p class="subtitle">
                “Tables turn, bridges burn, you live and learn.”
            </p>

            <div class="cta-row">
                <a class="btn btn-primary" href="<?= site_url('student/profile'); ?>">Go to Student Profile</a>
            </div>

            <div class="panel">
                <div class="panel-grid">
                    <div class="mini-card">
                        <div class="mini-label">Route</div>
                        <div class="mini-value">/student</div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-label">Controller</div>
                        <div class="mini-value">StudentController</div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-label">Status</div>
                        <div class="mini-value">Active</div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
