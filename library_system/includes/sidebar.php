<?php

$current = $_SERVER['REQUEST_URI'];

function nav_link($href, $icon, $label, $current)
{
    $active = (strpos($current, $href) !== false) ? 'active' : '';

    return "
    <a href='{$href}' class='{$active}'>
        {$icon}
        <span>{$label}</span>
    </a>";
}
?>

<aside class="sidebar">

    <div class="sidebar-logo">
        <h1>📚 LibraFlow</h1>
        <span>Library Management System</span>
    </div>

    <div class="nav-section">Main</div>

    <nav>

        <?= nav_link(
            BASE_URL . 'dashboard.php',
            '🏠',
            'Dashboard',
            $current
        ) ?>

        <div class="nav-section">Catalogue</div>

        <?= nav_link(
            BASE_URL . 'books/index.php',
            '📖',
            'Books',
            $current
        ) ?>

        <?= nav_link(
            BASE_URL . 'members/index.php',
            '👥',
            'Members',
            $current
        ) ?>

        <div class="nav-section">Circulation</div>

        <?= nav_link(
            BASE_URL . 'borrow/index.php',
            '🔄',
            'Borrow / Return',
            $current
        ) ?>

        <?= nav_link(
            BASE_URL . 'reservations/index.php',
            '🔖',
            'Reservations',
            $current
        ) ?>

        <?= nav_link(
            BASE_URL . 'reports/index.php',
            '📊',
            'Reports',
            $current
        ) ?>

    </nav>

    <div class="sidebar-footer">

        👤 <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?>

        <br><br>

        <a href="<?= BASE_URL ?>logout.php">
            Sign Out
        </a>

    </div>

</aside>