<?php
/*
index.php
*/
require __DIR__ . '/config/config.php';
require __DIR__ . '/helpers/helpers.php';
require __DIR__ . '/models/user_model.php';
check_session_timeout();
$page = $_GET['page'] ?? 'passenger';

switch ($page) {
    case 'login':
    case 'register':
    case 'logout':
    case 'forgot':
        require __DIR__ . '/controllers/auth_controller.php';
        break;
    case 'ajax':
        require __DIR__ . '/controllers/ajax_controller.php';
        break;
    case 'passenger':
        require __DIR__ . '/controllers/passenger_controller.php';
        break;
    case 'admin':
        require __DIR__ . '/controllers/admin_controller.php';
        break;
    case 'driver':
        require __DIR__ . '/controllers/driver_controller.php';
        break;
    case 'manager':
        require __DIR__ . '/controllers/manager_controller.php';
        break;
    case 'dashboard':
    default:
        // The public landing page doubles as the passenger dashboard/home.
        require __DIR__ . '/controllers/passenger_controller.php';
        break;
}