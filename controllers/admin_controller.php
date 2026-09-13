<?php
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/bus_model.php';
require_once __DIR__ . '/../models/route_model.php';
require_once __DIR__ . '/../models/trip_model.php';
require_once __DIR__ . '/../models/promo_model.php';
// require_once __DIR__ . '/../models/booking_model.php';
require_once __DIR__ . '/../models/feedback_model.php';
require_once __DIR__ . '/../models/report_model.php';
require_once __DIR__ . '/../models/incident_model.php';

global $conn;

// Only admins past this point
require_role('admin');

$action = $_GET['action'] ?? 'dashboard';


/*
==========================================================
ADMIN DASHBOARD
==========================================================
*/
if ($action === 'dashboard') {

    $perPage = 10;
    $page = max(1, (int) ($_GET['p'] ?? 1));

    $totalUsers = count_users($conn);
    $totalPages = max(1, (int) ceil($totalUsers / $perPage));
    $page = min($page, $totalPages);

    $users = get_users_paginated($conn, $page, $perPage);

    $pageTitle = 'Admin Dashboard';
    $pageHeading = 'Admin Dashboard';
    $pageSub = 'System overview and management';

    require __DIR__ . '/../views/admin/dashboard.php';
    exit;
}


/*
==========================================================
USER — UPDATE ROLE (POST + CSRF)
==========================================================
*/
if ($action === 'updateuserrole' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['user_id'] ?? 0);
    $newRole = clean_input($_POST['role'] ?? '');
    $backToPage = (int) ($_POST['p'] ?? 1);

    if (!in_array($newRole, ['passenger', 'driver', 'admin', 'manager'], true)) {
        set_flash('error', 'Please choose a valid role.');
        redirect('index.php?page=admin&action=dashboard&p=' . $backToPage);
    }

    if ($id === current_user_id()) {
        set_flash('error', 'You cannot change your own role.');
        redirect('index.php?page=admin&action=dashboard&p=' . $backToPage);
    }

    if ($id > 0 && update_user_role($conn, $id, $newRole)) {
        set_flash('success', 'User role updated.');
    } else {
        set_flash('error', 'Could not update role.');
    }
    redirect('index.php?page=admin&action=dashboard&p=' . $backToPage);
}


/*
==========================================================
BUS — ADD (POST)
==========================================================
*/
if ($action === 'addbus' && is_post()) {

    csrf_check();

    $busNumber = clean_input($_POST['bus_number'] ?? '');
    $name = clean_input($_POST['name'] ?? '');
    $type = clean_input($_POST['type'] ?? '');
    $seats = clean_input($_POST['total_seats'] ?? '');
    $status = clean_input($_POST['status'] ?? '');
    $limit = clean_input($_POST['service_trip_limit'] ?? '');

    if (is_blank($busNumber) || is_blank($name) || is_blank($seats) || is_blank($limit)) {
        set_flash('error', 'All fields are required.');
        redirect('index.php?page=admin&action=buses');
    }
    if (!in_array($type, ['AC', 'Non-AC'], true)) {
        set_flash('error', 'Please choose a valid bus type.');
        redirect('index.php?page=admin&action=buses');
    }
    if (!in_array($status, ['active', 'maintenance'], true)) {
        set_flash('error', 'Please choose a valid status.');
        redirect('index.php?page=admin&action=buses');
    }
    if (!ctype_digit($seats) || (int) $seats <= 0) {
        set_flash('error', 'Total seats must be a positive whole number.');
        redirect('index.php?page=admin&action=buses');
    }
    if (!ctype_digit($limit) || (int) $limit <= 0) {
        set_flash('error', 'Service trip limit must be a positive whole number.');
        redirect('index.php?page=admin&action=buses');
    }
    if (bus_number_exists($conn, $busNumber)) {
        set_flash('error', 'A bus with this number already exists.');
        redirect('index.php?page=admin&action=buses');
    }

    if (add_bus($conn, $busNumber, $name, $type, (int) $seats, $status, (int) $limit)) {
        set_flash('success', 'Bus added successfully.');
    } else {
        set_flash('error', 'Could not add the bus.');
    }
    redirect('index.php?page=admin&action=buses');
}


/*
==========================================================
BUS — UPDATE (POST)
==========================================================
*/
if ($action === 'updatebus' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['bus_id'] ?? 0);
    $busNumber = clean_input($_POST['bus_number'] ?? '');
    $name = clean_input($_POST['name'] ?? '');
    $type = clean_input($_POST['type'] ?? '');
    $seats = clean_input($_POST['total_seats'] ?? '');
    $status = clean_input($_POST['status'] ?? '');
    $limit = clean_input($_POST['service_trip_limit'] ?? '');

    if ($id <= 0 || !get_bus($conn, $id)) {
        set_flash('error', 'That bus could not be found.');
        redirect('index.php?page=admin&action=buses');
    }
    if (is_blank($busNumber) || is_blank($name) || is_blank($seats) || is_blank($limit)) {
        set_flash('error', 'All fields are required.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }
    if (!in_array($type, ['AC', 'Non-AC'], true)) {
        set_flash('error', 'Please choose a valid bus type.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }
    if (!in_array($status, ['active', 'maintenance'], true)) {
        set_flash('error', 'Please choose a valid status.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }
    if (!ctype_digit($seats) || (int) $seats <= 0) {
        set_flash('error', 'Total seats must be a positive whole number.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }
    if (!ctype_digit($limit) || (int) $limit <= 0) {
        set_flash('error', 'Service trip limit must be a positive whole number.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }
    // same number on a DIFFERENT bus?
    if (bus_number_exists($conn, $busNumber, $id)) {
        set_flash('error', 'Another bus already uses this number.');
        redirect('index.php?page=admin&action=buses&edit=' . $id);
    }

    if (update_bus($conn, $id, $busNumber, $name, $type, (int) $seats, $status, (int) $limit)) {
        set_flash('success', 'Bus updated.');
    } else {
        set_flash('error', 'Could not update the bus.');
    }
    redirect('index.php?page=admin&action=buses');
}


/*
==========================================================
BUS — DELETE (POST + CSRF)
==========================================================
*/
if ($action === 'deletebus' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['bus_id'] ?? 0);

    // Guard: don't delete a bus that is used by any trip
    $used = mysqli_prepare($conn, "SELECT trip_id FROM trips WHERE bus_id = ? LIMIT 1");
    mysqli_stmt_bind_param($used, 'i', $id);
    mysqli_stmt_execute($used);
    mysqli_stmt_store_result($used);
    $inUse = mysqli_stmt_num_rows($used) > 0;
    mysqli_stmt_close($used);

    if ($inUse) {
        set_flash('error', 'This bus is used by existing trips. Set it to "maintenance" instead of deleting.');
    } elseif ($id > 0 && delete_bus($conn, $id)) {
        set_flash('success', 'Bus deleted.');
    } else {
        set_flash('error', 'Could not delete bus.');
    }
    redirect('index.php?page=admin&action=buses');
}


/*
==========================================================
BUS — LIST + SEARCH (GET)  → shows the form + table
==========================================================
*/
if ($action === 'buses') {

    $search = clean_input($_GET['q'] ?? '');

    $buses = is_blank($search) ? get_buses($conn) : search_buses($conn, $search);

    // If ?edit=ID is present, load that bus into the form
    $editId = (int) ($_GET['edit'] ?? 0);
    $editBus = $editId ? get_bus($conn, $editId) : null;

    $pageTitle = 'Manage Buses';
    $pageHeading = 'Manage Buses';
    $pageSub = 'Add, edit, search and remove buses';

    require __DIR__ . '/../views/admin/buses.php';
    exit;
}

/*
==========================================================
ROUTE — ADD (POST)
==========================================================
*/
if ($action === 'addroute' && is_post()) {

    csrf_check();

    $origin = clean_input($_POST['origin'] ?? '');
    $destination = clean_input($_POST['destination'] ?? '');
    $distance = clean_input($_POST['distance_km'] ?? '');
    $duration = clean_input($_POST['duration'] ?? '');
    $stopsRaw = $_POST['stops'] ?? '';

    if (is_blank($origin) || is_blank($destination) || is_blank($distance)) {
        set_flash('error', 'Origin, destination and distance are required.');
        redirect('index.php?page=admin&action=routes');
    }
    if (!is_numeric($distance) || (float) $distance <= 0) {
        set_flash('error', 'Distance must be a positive number.');
        redirect('index.php?page=admin&action=routes');
    }

    // textarea -> array (one stop per line)
    $stops = array_filter(array_map('trim', explode("\n", $stopsRaw)), fn($s) => $s !== '');

    if (add_route($conn, $origin, $destination, (float) $distance, $duration, $stops)) {
        set_flash('success', 'Route added successfully.');
    } else {
        set_flash('error', 'Could not add the route.');
    }
    redirect('index.php?page=admin&action=routes');
}


/*
==========================================================
ROUTE — UPDATE (POST)
==========================================================
*/
if ($action === 'updateroute' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['route_id'] ?? 0);
    $origin = clean_input($_POST['origin'] ?? '');
    $destination = clean_input($_POST['destination'] ?? '');
    $distance = clean_input($_POST['distance_km'] ?? '');
    $duration = clean_input($_POST['duration'] ?? '');
    $stopsRaw = $_POST['stops'] ?? '';

    if ($id <= 0 || !get_route($conn, $id)) {
        set_flash('error', 'That route could not be found.');
        redirect('index.php?page=admin&action=routes');
    }
    if (is_blank($origin) || is_blank($destination) || is_blank($distance)) {
        set_flash('error', 'Origin, destination and distance are required.');
        redirect('index.php?page=admin&action=routes&edit=' . $id);
    }
    if (!is_numeric($distance) || (float) $distance <= 0) {
        set_flash('error', 'Distance must be a positive number.');
        redirect('index.php?page=admin&action=routes&edit=' . $id);
    }

    $stops = array_filter(array_map('trim', explode("\n", $stopsRaw)), fn($s) => $s !== '');

    if (update_route($conn, $id, $origin, $destination, (float) $distance, $duration, $stops)) {
        set_flash('success', 'Route updated.');
    } else {
        set_flash('error', 'Could not update the route.');
    }
    redirect('index.php?page=admin&action=routes');
}


/*
==========================================================
ROUTE — DELETE (POST + CSRF)
==========================================================
*/
if ($action === 'deleteroute' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['route_id'] ?? 0);

    // Guard: don't delete a route used by any trip
    $used = mysqli_prepare($conn, "SELECT trip_id FROM trips WHERE route_id = ? LIMIT 1");
    mysqli_stmt_bind_param($used, 'i', $id);
    mysqli_stmt_execute($used);
    mysqli_stmt_store_result($used);
    $inUse = mysqli_stmt_num_rows($used) > 0;
    mysqli_stmt_close($used);

    if ($inUse) {
        set_flash('error', 'This route is used by existing trips. Remove those trips first.');
    } elseif ($id > 0 && delete_route($conn, $id)) {
        set_flash('success', 'Route deleted.');
    } else {
        set_flash('error', 'Could not delete route.');
    }
    redirect('index.php?page=admin&action=routes');
}


/*
==========================================================
ROUTE — LIST + SEARCH (GET)
==========================================================
*/
if ($action === 'routes') {

    $search = clean_input($_GET['q'] ?? '');

    $routes = is_blank($search) ? get_routes($conn) : search_routes($conn, $search);

    $editId = (int) ($_GET['edit'] ?? 0);
    $editRoute = $editId ? get_route($conn, $editId) : null;
    $editStops = $editId ? get_route_stops($conn, $editId) : [];

    $pageTitle = 'Manage Routes';
    $pageHeading = 'Manage Routes';
    $pageSub = 'Add, edit, search and remove routes and their stops';

    require __DIR__ . '/../views/admin/routes.php';
    exit;
}


/*
==========================================================
PROMO — ADD (POST)
==========================================================
*/
if ($action === 'addpromo' && is_post()) {

    csrf_check();

    $code = clean_input($_POST['code'] ?? '');
    $type = clean_input($_POST['discount_type'] ?? '');
    $value = clean_input($_POST['discount_value'] ?? '');
    $expiry = clean_input($_POST['expiry_date'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (is_blank($code) || is_blank($value)) {
        set_flash('error', 'Code and discount value are required.');
        redirect('index.php?page=admin&action=promos');
    }
    if (!in_array($type, ['percent', 'flat'], true)) {
        set_flash('error', 'Please choose a valid discount type.');
        redirect('index.php?page=admin&action=promos');
    }
    if (!is_numeric($value) || (float) $value <= 0) {
        set_flash('error', 'Discount value must be a positive number.');
        redirect('index.php?page=admin&action=promos');
    }
    if ($type === 'percent' && (float) $value > 100) {
        set_flash('error', 'Percent discount cannot exceed 100.');
        redirect('index.php?page=admin&action=promos');
    }
    if (promo_code_exists($conn, $code)) {
        set_flash('error', 'This promo code already exists.');
        redirect('index.php?page=admin&action=promos');
    }

    $expiry = is_blank($expiry) ? null : $expiry;

    if (add_promo($conn, $code, $type, (float) $value, $expiry, $isActive)) {
        set_flash('success', 'Promo code added.');
    } else {
        set_flash('error', 'Could not add the promo code.');
    }
    redirect('index.php?page=admin&action=promos');
}


/*
==========================================================
PROMO — UPDATE (POST)
==========================================================
*/
if ($action === 'updatepromo' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['promo_id'] ?? 0);
    $code = clean_input($_POST['code'] ?? '');
    $type = clean_input($_POST['discount_type'] ?? '');
    $value = clean_input($_POST['discount_value'] ?? '');
    $expiry = clean_input($_POST['expiry_date'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($id <= 0 || !get_promo($conn, $id)) {
        set_flash('error', 'That promo code could not be found.');
        redirect('index.php?page=admin&action=promos');
    }
    if (is_blank($code) || is_blank($value)) {
        set_flash('error', 'Code and discount value are required.');
        redirect('index.php?page=admin&action=promos&edit=' . $id);
    }
    if (!in_array($type, ['percent', 'flat'], true)) {
        set_flash('error', 'Please choose a valid discount type.');
        redirect('index.php?page=admin&action=promos&edit=' . $id);
    }
    if (!is_numeric($value) || (float) $value <= 0) {
        set_flash('error', 'Discount value must be a positive number.');
        redirect('index.php?page=admin&action=promos&edit=' . $id);
    }
    if ($type === 'percent' && (float) $value > 100) {
        set_flash('error', 'Percent discount cannot exceed 100.');
        redirect('index.php?page=admin&action=promos&edit=' . $id);
    }
    if (promo_code_exists($conn, $code, $id)) {
        set_flash('error', 'Another promo already uses this code.');
        redirect('index.php?page=admin&action=promos&edit=' . $id);
    }

    $expiry = is_blank($expiry) ? null : $expiry;

    if (update_promo($conn, $id, $code, $type, (float) $value, $expiry, $isActive)) {
        set_flash('success', 'Promo code updated.');
    } else {
        set_flash('error', 'Could not update the promo code.');
    }
    redirect('index.php?page=admin&action=promos');
}


/*
==========================================================
PROMO — DELETE (POST + CSRF)
==========================================================
*/
if ($action === 'deletepromo' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['promo_id'] ?? 0);

    if ($id > 0 && delete_promo($conn, $id)) {
        set_flash('success', 'Promo code deleted.');
    } else {
        set_flash('error', 'Could not delete promo code.');
    }
    redirect('index.php?page=admin&action=promos');
}


/*
==========================================================
PROMO — LIST + SEARCH (GET)
==========================================================
*/
if ($action === 'promos') {

    $search = clean_input($_GET['q'] ?? '');

    $promos = is_blank($search) ? get_promos($conn) : search_promos($conn, $search);

    $editId = (int) ($_GET['edit'] ?? 0);
    $editPromo = $editId ? get_promo($conn, $editId) : null;

    $pageTitle = 'Manage Promo Codes';
    $pageHeading = 'Manage Promo Codes';
    $pageSub = 'Add, edit, search and remove discount codes';

    require __DIR__ . '/../views/admin/promos.php';
    exit;
}

/*
==========================================================
TRIP — ADD (POST)
==========================================================
*/
if ($action === 'addtrip' && is_post()) {

    csrf_check();

    $busId = (int) ($_POST['bus_id'] ?? 0);
    $routeId = (int) ($_POST['route_id'] ?? 0);
    $driverId = (int) ($_POST['driver_id'] ?? 0);
    $date = clean_input($_POST['trip_date'] ?? '');
    $depart = clean_input($_POST['departure_time'] ?? '');
    $arrive = clean_input($_POST['arrival_time'] ?? '');
    $fare = clean_input($_POST['fare'] ?? '');
    $status = clean_input($_POST['status'] ?? 'scheduled');

    $bus = get_bus($conn, $busId);

    if (!$bus || !get_route($conn, $routeId) || $driverId <= 0) {
        set_flash('error', 'Please choose a valid bus, route and driver.');
        redirect('index.php?page=admin&action=trips');
    }
    if (is_blank($date) || is_blank($depart) || is_blank($arrive) || is_blank($fare)) {
        set_flash('error', 'Date, times and fare are required.');
        redirect('index.php?page=admin&action=trips');
    }
    if (!is_numeric($fare) || (float) $fare <= 0) {
        set_flash('error', 'Fare must be a positive number.');
        redirect('index.php?page=admin&action=trips');
    }
    if (!in_array($status, ['scheduled', 'running', 'completed', 'cancelled'], true)) {
        set_flash('error', 'Please choose a valid status.');
        redirect('index.php?page=admin&action=trips');
    }

    // new trip → all seats free
    $seats = (int) $bus['total_seats'];

    if (add_trip($conn, $busId, $routeId, $driverId, $date, $depart, $arrive, (float) $fare, $seats, $status)) {
        set_flash('success', 'Trip scheduled successfully.');
    } else {
        set_flash('error', 'Could not schedule the trip.');
    }
    redirect('index.php?page=admin&action=trips');
}


/*
==========================================================
TRIP — UPDATE (POST)
==========================================================
*/
if ($action === 'updatetrip' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['trip_id'] ?? 0);
    $busId = (int) ($_POST['bus_id'] ?? 0);
    $routeId = (int) ($_POST['route_id'] ?? 0);
    $driverId = (int) ($_POST['driver_id'] ?? 0);
    $date = clean_input($_POST['trip_date'] ?? '');
    $depart = clean_input($_POST['departure_time'] ?? '');
    $arrive = clean_input($_POST['arrival_time'] ?? '');
    $fare = clean_input($_POST['fare'] ?? '');
    $seats = clean_input($_POST['available_seats'] ?? '');
    $status = clean_input($_POST['status'] ?? 'scheduled');

    if ($id <= 0 || !get_trip($conn, $id)) {
        set_flash('error', 'That trip could not be found.');
        redirect('index.php?page=admin&action=trips');
    }
    if (!get_bus($conn, $busId) || !get_route($conn, $routeId) || $driverId <= 0) {
        set_flash('error', 'Please choose a valid bus, route and driver.');
        redirect('index.php?page=admin&action=trips&edit=' . $id);
    }
    if (is_blank($date) || is_blank($depart) || is_blank($arrive) || is_blank($fare)) {
        set_flash('error', 'Date, times and fare are required.');
        redirect('index.php?page=admin&action=trips&edit=' . $id);
    }
    if (!is_numeric($fare) || (float) $fare <= 0) {
        set_flash('error', 'Fare must be a positive number.');
        redirect('index.php?page=admin&action=trips&edit=' . $id);
    }
    if (!ctype_digit($seats)) {
        set_flash('error', 'Available seats must be a whole number.');
        redirect('index.php?page=admin&action=trips&edit=' . $id);
    }
    if (!in_array($status, ['scheduled', 'running', 'completed', 'cancelled'], true)) {
        set_flash('error', 'Please choose a valid status.');
        redirect('index.php?page=admin&action=trips&edit=' . $id);
    }

    if (update_trip($conn, $id, $busId, $routeId, $driverId, $date, $depart, $arrive, (float) $fare, (int) $seats, $status)) {
        set_flash('success', 'Trip updated.');
    } else {
        set_flash('error', 'Could not update the trip.');
    }
    redirect('index.php?page=admin&action=trips');
}


/*
==========================================================
TRIP — DELETE (POST + CSRF)
==========================================================
*/
if ($action === 'deletetrip' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['trip_id'] ?? 0);

    // Guard: don't delete a trip that has bookings
    $used = mysqli_prepare($conn, "SELECT booking_id FROM bookings WHERE trip_id = ? LIMIT 1");
    mysqli_stmt_bind_param($used, 'i', $id);
    mysqli_stmt_execute($used);
    mysqli_stmt_store_result($used);
    $inUse = mysqli_stmt_num_rows($used) > 0;
    mysqli_stmt_close($used);

    if ($inUse) {
        set_flash('error', 'Tickets are sold for this trip. Set its status to "cancelled" instead of deleting.');
    } elseif ($id > 0 && delete_trip($conn, $id)) {
        set_flash('success', 'Trip deleted.');
    } else {
        set_flash('error', 'Could not delete trip.');
    }
    redirect('index.php?page=admin&action=trips');
}


/*
==========================================================
TRIP — LIST + SEARCH (GET)
==========================================================
*/
if ($action === 'trips') {

    $search = clean_input($_GET['q'] ?? '');

    $trips = is_blank($search) ? get_trips($conn) : search_trips_admin($conn, $search);

    // dropdown data
    $allBuses = get_buses($conn);
    $allRoutes = get_routes($conn);
    $allDrivers = get_drivers();

    $editId = (int) ($_GET['edit'] ?? 0);
    $editTrip = $editId ? get_trip($conn, $editId) : null;

    $pageTitle = 'Manage Trips';
    $pageHeading = 'Manage Trips';
    $pageSub = 'Schedule and manage bus trips';

    require __DIR__ . '/../views/admin/trips.php';
    exit;
}

/*
==========================================================
FEEDBACK — LIST + SEARCH (GET, read-only)
==========================================================
*/
if ($action === 'feedback') {

    $search = clean_input($_GET['q'] ?? '');

    $feedback = is_blank($search) ? get_all_feedback($conn) : search_feedback($conn, $search);

    $pageTitle = 'Feedback';
    $pageHeading = 'User Feedback';
    $pageSub = 'What passengers are saying';

    require __DIR__ . '/../views/admin/feedback.php';
    exit;
}

/*
==========================================================
REVENUE — REPORT (GET, read-only)
==========================================================
*/
if ($action === 'revenue') {

    $summary = get_revenue_summary($conn);
    $revenueByRoute = get_revenue_by_route($conn);

    $pageTitle = 'Revenue & Reports';
    $pageHeading = 'Revenue & Reports';
    $pageSub = 'Earnings from paid bookings';

    require __DIR__ . '/../views/admin/revenue.php';
    exit;
}

/*
==========================================================
DAMAGE — RESOLVE (POST + CSRF)
==========================================================
*/
if ($action === 'resolvedamage' && is_post()) {

    csrf_check();

    $id = (int) ($_POST['incident_id'] ?? 0);

    if ($id > 0 && resolve_damage_report($conn, $id)) {
        set_flash('success', 'Damage report marked as resolved.');
    } else {
        set_flash('error', 'Could not resolve — it may already be closed.');
    }
    redirect('index.php?page=admin&action=damage');
}


/*
==========================================================
DAMAGE — LIST + SEARCH (GET)
==========================================================
*/
if ($action === 'damage') {

    $search = clean_input($_GET['q'] ?? '');

    $reports = is_blank($search) ? get_damage_reports($conn) : search_damage_reports($conn, $search);

    $pageTitle = 'Damage Reports';
    $pageHeading = 'Damage Reports';
    $pageSub = 'Bus damage reported by drivers';

    require __DIR__ . '/../views/admin/damage.php';
    exit;
}

// fallback
redirect('index.php?page=admin');
