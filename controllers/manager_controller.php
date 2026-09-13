<?php
// controllers/manager_controller.php

require_role('manager');

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

$manager_id = $_SESSION['user']['user_id'];

if ($action == 'dashboard') {
    require_once __DIR__ . '/../models/maintenance_model.php';
    require_once __DIR__ . '/../models/part_model.php';
    require_once __DIR__ . '/../models/service_model.php';

    $busesNeedingService = get_buses_needing_service($conn);
    $openRequests        = get_open_requests($conn, $manager_id);
    $lowStockParts       = get_low_stock_parts($conn);
    $costReport          = get_maintenance_cost_report($conn, $manager_id);

    $pageTitle = 'Manager Dashboard';
    require __DIR__ . '/../views/manager/dashboard.php';
}
elseif ($action == 'parts') {
    require_once __DIR__ . '/../models/part_model.php';

    $editing = null;
    if (isset($_GET['edit'])) {
        $editing = get_part($conn, (int) $_GET['edit']);
    }

    $q = clean_input($_GET['q'] ?? '');
    $partList = is_blank($q) ? get_all_parts($conn) : search_parts($conn, $q);

    $pageTitle = 'Spare Parts';
    require __DIR__ . '/../views/manager/parts.php';
}
elseif ($action == 'addpart') {
    require_once __DIR__ . '/../models/part_model.php';
    csrf_check();

    $name  = clean_input($_POST['part_name'] ?? '');
    $qty   = (int) ($_POST['stock_quantity'] ?? 0);
    $price = (float) ($_POST['unit_price'] ?? 0);

    if (is_blank($name)) {
        set_old($_POST);
        set_flash('error', 'Part name is required.');
    } elseif ($qty < 0 || $price < 0) {
        set_old($_POST);
        set_flash('error', 'Quantity and price must not be negative.');
    } else {
        add_part($conn, $name, $qty, $price);
        clear_old();
        set_flash('success', 'Part added.');
    }
    redirect('index.php?page=manager&action=parts');
}
elseif ($action == 'updatepart') {
    require_once __DIR__ . '/../models/part_model.php';
    csrf_check();

    $id    = (int) ($_GET['id'] ?? 0);
    $name  = clean_input($_POST['part_name'] ?? '');
    $qty   = (int) ($_POST['stock_quantity'] ?? 0);
    $price = (float) ($_POST['unit_price'] ?? 0);

    if (is_blank($name)) {
        set_flash('error', 'Part name is required.');
        redirect('index.php?page=manager&action=parts&edit=' . $id);
    } elseif ($qty < 0 || $price < 0) {
        set_flash('error', 'Quantity and price must not be negative.');
        redirect('index.php?page=manager&action=parts&edit=' . $id);
    } else {
        update_part($conn, $id, $name, $qty, $price);
        set_flash('success', 'Part updated.');
        redirect('index.php?page=manager&action=parts');
    }
}
elseif ($action == 'deletepart') {
    require_once __DIR__ . '/../models/part_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    delete_part($conn, $id);
    set_flash('success', 'Part deleted.');
    redirect('index.php?page=manager&action=parts');
}
elseif ($action == 'requests') {
    require_once __DIR__ . '/../models/maintenance_model.php';

    $q = clean_input($_GET['q'] ?? '');
    $requestList = is_blank($q)
        ? get_manager_requests($conn, $manager_id)
        : search_manager_requests($conn, $manager_id, $q);

    $busList = get_buses_for_dropdown($conn);

    $pageTitle = 'Maintenance Requests';
    require __DIR__ . '/../views/manager/requests.php';
}
elseif ($action == 'addrequest') {
    require_once __DIR__ . '/../models/maintenance_model.php';
    csrf_check();

    $bus_id = (int) ($_POST['bus_id'] ?? 0);
    $issue  = clean_input($_POST['issue'] ?? '');

    if ($bus_id <= 0) {
        set_old($_POST);
        set_flash('error', 'Please select a bus.');
    } elseif (is_blank($issue)) {
        set_old($_POST);
        set_flash('error', 'Please describe the issue.');
    } else {
        add_request($conn, $bus_id, $manager_id, $issue);
        clear_old();
        set_flash('success', 'Maintenance request submitted.');
    }
    redirect('index.php?page=manager&action=requests');
}
elseif ($action == 'updaterequest') {
    require_once __DIR__ . '/../models/maintenance_model.php';
    csrf_check();

    $id     = (int) ($_GET['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!in_array($status, ['in_progress', 'done'], true)) {
        set_flash('error', 'Invalid status change.');
    } else {
        update_request_status($conn, $id, $manager_id, $status);
        set_flash('success', 'Request updated.');
    }
    redirect('index.php?page=manager&action=requests');
}
elseif ($action == 'cancelrequest') {
    require_once __DIR__ . '/../models/maintenance_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    cancel_request($conn, $id, $manager_id);
    set_flash('success', 'Request cancelled.');
    redirect('index.php?page=manager&action=requests');
}
elseif ($action == 'services') {
    require_once __DIR__ . '/../models/service_model.php';
    require_once __DIR__ . '/../models/maintenance_model.php';
    require_once __DIR__ . '/../models/part_model.php';

    $editing = null;
    if (isset($_GET['edit'])) {
        $editing = get_service($conn, (int) $_GET['edit'], $manager_id);
    }

    $q = clean_input($_GET['q'] ?? '');
    $serviceList = is_blank($q)
        ? get_manager_services($conn, $manager_id)
        : search_manager_services($conn, $manager_id, $q);

    foreach ($serviceList as &$s) {
        $s['parts_used'] = get_parts_for_service($conn, $s['service_id']);
    }
    unset($s);

    $busList  = get_buses_for_dropdown($conn);
    $partList = get_all_parts($conn);

    $pageTitle = 'Service History';
    require __DIR__ . '/../views/manager/services.php';
}
elseif ($action == 'addservice') {
    require_once __DIR__ . '/../models/service_model.php';
    csrf_check();

    $bus_id       = (int) ($_POST['bus_id'] ?? 0);
    $service_date = $_POST['service_date'] ?? '';
    $work_done    = clean_input($_POST['work_done'] ?? '');
    $cost         = (float) ($_POST['cost'] ?? 0);

    if ($bus_id <= 0) {
        set_old($_POST);
        set_flash('error', 'Please select a bus.');
        redirect('index.php?page=manager&action=services');
    }
    if (is_blank($service_date) || !valid_date($service_date)) {
        set_old($_POST);
        set_flash('error', 'Please enter a valid service date.');
        redirect('index.php?page=manager&action=services');
    }
    if (is_blank($work_done)) {
        set_old($_POST);
        set_flash('error', 'Please describe the work done.');
        redirect('index.php?page=manager&action=services');
    }
    if ($cost < 0) {
        set_old($_POST);
        set_flash('error', 'Cost must not be negative.');
        redirect('index.php?page=manager&action=services');
    }

    $service_id = add_service($conn, $bus_id, $manager_id, $service_date, $work_done, $cost);

    $partIds  = $_POST['part_id'] ?? [];
    $partQtys = $_POST['part_qty'] ?? [];

    foreach ($partIds as $i => $partId) {
        $partId = (int) $partId;
        $qty    = (int) ($partQtys[$i] ?? 0);

        if ($partId > 0 && $qty > 0) {
            add_service_part($conn, $service_id, $partId, $qty);
            decrement_part_stock($conn, $partId, $qty);
        }
    }

    clear_old();
    set_flash('success', 'Service record added.');
    redirect('index.php?page=manager&action=services');
}
elseif ($action == 'updateservice') {
    require_once __DIR__ . '/../models/service_model.php';
    csrf_check();

    $id           = (int) ($_GET['id'] ?? 0);
    $bus_id       = (int) ($_POST['bus_id'] ?? 0);
    $service_date = $_POST['service_date'] ?? '';
    $work_done    = clean_input($_POST['work_done'] ?? '');
    $cost         = (float) ($_POST['cost'] ?? 0);

    if ($bus_id <= 0 || is_blank($service_date) || !valid_date($service_date) || is_blank($work_done) || $cost < 0) {
        set_flash('error', 'Please fill all fields correctly.');
        redirect('index.php?page=manager&action=services&edit=' . $id);
    } else {
        update_service($conn, $id, $manager_id, $bus_id, $service_date, $work_done, $cost);
        set_flash('success', 'Service record updated.');
        redirect('index.php?page=manager&action=services');
    }
}
else {
    header("Location: index.php?page=manager");
    exit();
}