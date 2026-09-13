<?php

/*
==========================================================
views/partials/navbar.php
Role-aware menu: admin / driver / manager / passenger
==========================================================
*/

$navUser = current_user();
$loggedIn = is_logged_in();
$activePage = $_GET['action'] ?? 'dashboard';
$role = current_role();

?>

<header class="navbar">
    <div class="navbar-inner">

        <a class="brand" href="index.php?page=<?= $loggedIn ? esc($role) : 'passenger' ?>">
            <span class="brand-icon">&#128652;</span>
            <span><?= esc(APP_NAME) ?></span>
        </a>

        <nav class="nav-menu">

            <?php if ($role === 'admin'): ?>

                <a href="index.php?page=admin" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="index.php?page=admin&action=buses" class="<?= $activePage === 'buses' ? 'active' : '' ?>">Buses</a>
                <a href="index.php?page=admin&action=routes"
                    class="<?= $activePage === 'routes' ? 'active' : '' ?>">Routes</a>
                <a href="index.php?page=admin&action=trips" class="<?= $activePage === 'trips' ? 'active' : '' ?>">Trips</a>
                <a href="index.php?page=admin&action=promos"
                    class="<?= $activePage === 'promos' ? 'active' : '' ?>">Promos</a>
                <a href="index.php?page=admin&action=revenue"
                    class="<?= $activePage === 'revenue' ? 'active' : '' ?>">Revenue</a>
                <a href="index.php?page=admin&action=feedback"
                    class="<?= $activePage === 'feedback' ? 'active' : '' ?>">Feedback</a>
                <a href="index.php?page=admin&action=damage"
                    class="<?= $activePage === 'damage' ? 'active' : '' ?>">Damage</a>

            <?php elseif ($role === 'driver'): ?>

                <a href="index.php?page=driver" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="index.php?page=driver&action=availability"
                    class="<?= $activePage === 'availability' ? 'active' : '' ?>">Availability</a><a
                    href="index.php?page=driver&action=incidents"
                    class="<?= $activePage === 'incidents' ? 'active' : '' ?>">Incidents</a>
                <a href="index.php?page=driver&action=logs" class="<?= $activePage === 'logs' ? 'active' : '' ?>">Logs</a>
                <a href="index.php?page=driver&action=verify"
                    class="<?= $activePage === 'verify' ? 'active' : '' ?>">Verify</a>

            <?php elseif ($role === 'manager'): ?>

                <a href="index.php?page=manager" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="index.php?page=manager&action=parts"
                    class="<?= $activePage === 'parts' ? 'active' : '' ?>">Parts</a>
                <a href="index.php?page=manager&action=requests"
                    class="<?= $activePage === 'requests' ? 'active' : '' ?>">Requests</a>
                <a href="index.php?page=manager&action=services"
                    class="<?= $activePage === 'services' ? 'active' : '' ?>">Services</a>

            <?php elseif ($role === 'passenger'): ?>

                <a href="index.php?page=passenger" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Home</a>
                <a href="index.php?page=passenger&action=search"
                    class="<?= $activePage === 'search' ? 'active' : '' ?>">Search Bus</a>
                <a href="index.php?page=passenger&action=busratings"
                    class="<?= $activePage === 'busratings' ? 'active' : '' ?>">Bus Ratings</a>

                <?php if ($loggedIn): ?>
                    <a href="index.php?page=passenger&action=mytickets"
                        class="<?= $activePage === 'mytickets' ? 'active' : '' ?>">My Tickets</a>
                    <a href="index.php?page=passenger&action=myreviews"
                        class="<?= $activePage === 'myreviews' ? 'active' : '' ?>">Reviews</a>
                <?php endif; ?>

                <a href="index.php?page=passenger&action=feedback"
                    class="<?= $activePage === 'feedback' ? 'active' : '' ?>">Feedback</a>

            <?php endif; ?>

        </nav>

        <div class="nav-user">

            <?php if ($loggedIn): ?>

                <a href="index.php?page=<?= $role === 'passenger' ? 'passenger&action=profile' : esc($role) ?>"
                    class="user-pill">
                    <span class="user-avatar"><?= esc(strtoupper(substr($navUser['name'] ?? 'U', 0, 1))) ?></span>
                    <span class="user-meta">
                        <span class="user-name"><?= esc($navUser['name'] ?? 'User') ?></span>
                        <span class="user-role"><?= esc(ucfirst($role)) ?></span>
                    </span>
                </a>

                <a href="index.php?page=logout" class="btn-logout">Sign out</a>

            <?php else: ?>

                <a href="index.php?page=login" class="btn btn-ghost">Login</a>
                <a href="index.php?page=register" class="btn btn-primary">Register</a>

            <?php endif; ?>

        </div>

    </div>
</header>