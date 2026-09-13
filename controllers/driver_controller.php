<?php
// controllers/driver_controller.php

require_role('driver');

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

$driver_id = $_SESSION['user']['user_id'];

if ($action == 'dashboard') {
    require_once __DIR__ . '/../models/triplog_model.php';

    $today = date('Y-m-d');
    $trips = get_driver_trips($conn, $driver_id, $today);

    require __DIR__ . '/../views/driver/dashboard.php';
} elseif ($action == 'availability') {
    require_once __DIR__ . '/../models/availability_model.php';

    $editing = null;
    if (isset($_GET['edit'])) {
        $editing = get_availability($conn, (int) $_GET['edit'], $driver_id);
    }

    $availabilityList = get_driver_availability($conn, $driver_id);

    $pageTitle = 'My Availability';
    require __DIR__ . '/../views/driver/availability.php';
} elseif ($action == 'addavailability') {
    require_once __DIR__ . '/../models/availability_model.php';
    csrf_check();

    $date = $_POST['date'] ?? '';
    $status = $_POST['status'] ?? '';
    $note = clean_input($_POST['note'] ?? '');

    if (is_blank($date) || !valid_date($date)) {
        set_old($_POST);
        set_flash('error', 'Please enter a valid date.');
    } elseif (!in_array($status, ['available', 'off_day', 'on_duty'], true)) {
        set_old($_POST);
        set_flash('error', 'Please select a valid status.');
    } else {
        add_availability($conn, $driver_id, $date, $status, $note);
        clear_old();
        set_flash('success', 'Availability added.');
    }
    redirect('index.php?page=driver&action=availability');
} elseif ($action == 'updateavailability') {
    require_once __DIR__ . '/../models/availability_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $status = $_POST['status'] ?? '';
    $note = clean_input($_POST['note'] ?? '');

    if (is_blank($date) || !valid_date($date)) {
        set_flash('error', 'Please enter a valid date.');
        redirect('index.php?page=driver&action=availability&edit=' . $id);
    } elseif (!in_array($status, ['available', 'off_day', 'on_duty'], true)) {
        set_flash('error', 'Please select a valid status.');
        redirect('index.php?page=driver&action=availability&edit=' . $id);
    } else {
        update_availability($conn, $id, $driver_id, $date, $status, $note);
        set_flash('success', 'Availability updated.');
        redirect('index.php?page=driver&action=availability');
    }
} elseif ($action == 'deleteavailability') {
    require_once __DIR__ . '/../models/availability_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    delete_availability($conn, $id, $driver_id);
    set_flash('success', 'Availability deleted.');
    redirect('index.php?page=driver&action=availability');
} elseif ($action == 'incidents') {
    require_once __DIR__ . '/../models/incident_model.php';
    require_once __DIR__ . '/../models/triplog_model.php';

    $editing = null;
    if (isset($_GET['edit'])) {
        $editing = get_driver_incident($conn, (int) $_GET['edit'], $driver_id);
    }

    $incidentList = get_driver_incidents($conn, $driver_id);
    $myTrips = get_driver_trips($conn, $driver_id);

    $pageTitle = 'Incident Reports';
    require __DIR__ . '/../views/driver/incidents.php';
} elseif ($action == 'addincident') {
    require_once __DIR__ . '/../models/incident_model.php';
    require_once __DIR__ . '/../models/triplog_model.php';
    csrf_check();

    $trip_id = !empty($_POST['trip_id']) ? (int) $_POST['trip_id'] : null;
    $type = $_POST['type'] ?? '';
    $description = clean_input($_POST['description'] ?? '');

    if (!in_array($type, ['incident', 'damage'], true)) {
        set_old($_POST);
        set_flash('error', 'Please select a valid type.');
    } elseif (is_blank($description)) {
        set_old($_POST);
        set_flash('error', 'Description is required.');
    } else {
        if ($trip_id && !trip_belongs_to_driver($conn, $trip_id, $driver_id)) {
            $trip_id = null;
        }
        $bus_id = $trip_id ? get_bus_id_for_trip($conn, $trip_id) : null;

        add_incident($conn, $driver_id, $trip_id, $bus_id, $type, $description);
        clear_old();
        set_flash('success', 'Report submitted.');
    }
    redirect('index.php?page=driver&action=incidents');
} elseif ($action == 'updateincident') {
    require_once __DIR__ . '/../models/incident_model.php';
    require_once __DIR__ . '/../models/triplog_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    $trip_id = !empty($_POST['trip_id']) ? (int) $_POST['trip_id'] : null;
    $type = $_POST['type'] ?? '';
    $description = clean_input($_POST['description'] ?? '');

    if (!in_array($type, ['incident', 'damage'], true)) {
        set_flash('error', 'Please select a valid type.');
        redirect('index.php?page=driver&action=incidents&edit=' . $id);
    } elseif (is_blank($description)) {
        set_flash('error', 'Description is required.');
        redirect('index.php?page=driver&action=incidents&edit=' . $id);
    } else {
        if ($trip_id && !trip_belongs_to_driver($conn, $trip_id, $driver_id)) {
            $trip_id = null;
        }
        $bus_id = $trip_id ? get_bus_id_for_trip($conn, $trip_id) : null;

        update_incident($conn, $id, $driver_id, $trip_id, $bus_id, $type, $description);
        set_flash('success', 'Report updated.');
        redirect('index.php?page=driver&action=incidents');
    }
} elseif ($action == 'deleteincident') {
    require_once __DIR__ . '/../models/incident_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    delete_incident($conn, $id, $driver_id);
    set_flash('success', 'Report deleted.');
    redirect('index.php?page=driver&action=incidents');
} elseif ($action == 'logs') {
    require_once __DIR__ . '/../models/triplog_model.php';

    $logList = get_driver_logs($conn, $driver_id);

    $pageTitle = 'Trip Logs';
    require __DIR__ . '/../views/driver/logs.php';
} elseif ($action == 'startlog') {
    require_once __DIR__ . '/../models/triplog_model.php';
    csrf_check();

    $trip_id = (int) ($_POST['trip_id'] ?? 0);

    if ($trip_id && trip_belongs_to_driver($conn, $trip_id, $driver_id)) {
        start_trip_log($conn, $trip_id, $driver_id);
        set_flash('success', 'Trip started.');
    } else {
        set_flash('error', 'Trip not found, or it is not assigned to you.');
    }
    redirect('index.php?page=driver&action=dashboard');
} elseif ($action == 'updatelog') {
    require_once __DIR__ . '/../models/triplog_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);

    if (complete_trip_log($conn, $id, $driver_id)) {
        set_flash('success', 'Trip completed.');
    } else {
        set_flash('error', 'Log not found, or it does not belong to you.');
    }
    redirect('index.php?page=driver&action=logs');
} elseif ($action == 'deletelog') {
    require_once __DIR__ . '/../models/triplog_model.php';
    csrf_check();

    $id = (int) ($_GET['id'] ?? 0);
    delete_log($conn, $id, $driver_id);
    set_flash('success', 'Log deleted.');
    redirect('index.php?page=driver&action=logs');
} elseif ($action == 'verify') {
    $pageTitle = 'Verify Passenger';
    require __DIR__ . '/../views/driver/verify.php';
} else {
    header("Location: index.php?page=driver");
    exit();
}