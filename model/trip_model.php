<?php
// models/trip_model.php



// Whitelisted sort options for search_trips() — never interpolate raw user input into ORDER BY
const TRIP_SORT_COLUMNS = [
    'departure' => 't.departure_time ASC',
    'fare_low' => 't.fare ASC',
    'fare_high' => 't.fare DESC',
];


function search_trips($from = '', $to = '', $date = '', $type = '', $sort = 'departure')
{
    global $conn;

    $orderBy = TRIP_SORT_COLUMNS[$sort] ?? TRIP_SORT_COLUMNS['departure'];

    $sql = "
        SELECT
            t.trip_id, t.bus_id, t.route_id, t.trip_date, t.departure_time,
            t.arrival_time, t.fare, t.available_seats, t.status,
            b.name AS bus_name, b.bus_number, b.type AS bus_type, b.total_seats,
            r.origin, r.destination, r.duration, r.distance_km,
            COALESCE(AVG(rv.rating), 0) AS avg_rating,
            COUNT(DISTINCT rv.review_id) AS review_count
        FROM trips t
        INNER JOIN buses b  ON t.bus_id = b.bus_id
        INNER JOIN routes r ON t.route_id = r.route_id
        LEFT JOIN reviews rv ON rv.bus_id = b.bus_id
        WHERE t.status = 'scheduled'
    ";

    $types = "";
    $params = [];

    if ($from !== '') {
        $sql .= " AND r.origin = ? ";
        $types .= "s";
        $params[] = $from;
    }

    if ($to !== '') {
        $sql .= " AND r.destination = ? ";
        $types .= "s";
        $params[] = $to;
    }

    if ($date !== '') {
        $sql .= " AND t.trip_date = ? ";
        $types .= "s";
        $params[] = $date;
    } else {
        // No date chosen — still hide trips that have already left
        $sql .= " AND t.trip_date >= CURDATE() ";
    }

    if ($type === 'AC' || $type === 'Non-AC') {
        $sql .= " AND b.type = ? ";
        $types .= "s";
        $params[] = $type;
    }

    $sql .= " GROUP BY t.trip_id ORDER BY t.trip_date ASC, {$orderBy} ";

    $stmt = mysqli_prepare($conn, $sql);

    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $trips = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $trips[] = $row;
    }

    return $trips;
}


function get_trip_details($trip_id)
{
    global $conn;

    $sql = "
        SELECT
            t.trip_id, t.bus_id, t.route_id, t.trip_date, t.departure_time,
            t.arrival_time, t.fare, t.available_seats, t.status,
            b.name AS bus_name, b.bus_number, b.type AS bus_type, b.total_seats,
            r.origin, r.destination, r.duration, r.distance_km,
            COALESCE(AVG(rv.rating), 0) AS avg_rating,
            COUNT(DISTINCT rv.review_id) AS review_count
        FROM trips t
        INNER JOIN buses b  ON t.bus_id = b.bus_id
        INNER JOIN routes r ON t.route_id = r.route_id
        LEFT JOIN reviews rv ON rv.bus_id = b.bus_id
        WHERE t.trip_id = ?
        GROUP BY t.trip_id
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $trip_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


// Lightweight fetch used internally during booking validation (no joins/aggregates)
function get_trip_basic($trip_id)
{
    global $conn;

    $sql = "SELECT * FROM trips WHERE trip_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $trip_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


// Atomic, race-safe: only succeeds if enough seats are still available at update time
function decrement_trip_seats($trip_id, $seats)
{
    global $conn;

    $sql = "UPDATE trips SET available_seats = available_seats - ? WHERE trip_id = ? AND available_seats >= ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $seats, $trip_id, $seats);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_affected_rows($stmt) > 0;
}


function increment_trip_seats($trip_id, $seats)
{
    global $conn;

    $sql = "UPDATE trips SET available_seats = available_seats + ? WHERE trip_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $seats, $trip_id);

    return mysqli_stmt_execute($stmt);
}


function get_popular_routes($limit = 4)
{
    global $conn;

    $sql = "
        SELECT r.route_id, r.origin, r.destination, MIN(t.fare) AS min_fare, COUNT(*) AS trip_count
        FROM routes r
        INNER JOIN trips t ON t.route_id = r.route_id
        WHERE t.status = 'scheduled' AND t.trip_date >= CURDATE()
        GROUP BY r.route_id
        ORDER BY trip_count DESC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $routes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $routes[] = $row;
    }

    return $routes;
}
// ==========================================
// MODEL: trips (Admin CRUD module)
// Handles the core schedule joining buses, routes, and drivers
// ==========================================

function get_trips($conn)
{
    $sql = "SELECT t.trip_id, t.trip_date, t.departure_time, t.arrival_time, t.fare, t.available_seats, t.status,
                   b.bus_number, b.name AS bus_name, 
                   r.origin, r.destination, 
                   u.name AS driver_name
            FROM trips t
            JOIN buses b ON t.bus_id = b.bus_id
            JOIN routes r ON t.route_id = r.route_id
            JOIN users u ON t.driver_id = u.user_id
            ORDER BY t.trip_date DESC, t.departure_time ASC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_trip($conn, $id)
{
    $sql = "SELECT trip_id, bus_id, route_id, driver_id, trip_date, departure_time, arrival_time, fare, available_seats, status 
            FROM trips WHERE trip_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function search_trips_admin($conn, $term)
{
    $like = '%' . $term . '%';
    $sql = "SELECT t.trip_id, t.trip_date, t.departure_time, t.arrival_time, t.fare, t.available_seats, t.status,
                   b.bus_number, b.name AS bus_name, 
                   r.origin, r.destination, 
                   u.name AS driver_name
            FROM trips t
            JOIN buses b ON t.bus_id = b.bus_id
            JOIN routes r ON t.route_id = r.route_id
            JOIN users u ON t.driver_id = u.user_id
            WHERE b.bus_number LIKE ? OR r.origin LIKE ? OR r.destination LIKE ? OR u.name LIKE ? OR t.status LIKE ?
            ORDER BY t.trip_date DESC, t.departure_time ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssss', $like, $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function add_trip($conn, $bus_id, $route_id, $driver_id, $trip_date, $departure_time, $arrival_time, $fare, $available_seats, $status)
{
    $sql = "INSERT INTO trips (bus_id, route_id, driver_id, trip_date, departure_time, arrival_time, fare, available_seats, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: int, int, int, string(date), string(time), string(time), double(decimal), int, string(enum) -> 'iiisssdis'
    mysqli_stmt_bind_param($stmt, 'iiisssdis', $bus_id, $route_id, $driver_id, $trip_date, $departure_time, $arrival_time, $fare, $available_seats, $status);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_trip($conn, $id, $bus_id, $route_id, $driver_id, $trip_date, $departure_time, $arrival_time, $fare, $available_seats, $status)
{
    $sql = "UPDATE trips 
            SET bus_id = ?, route_id = ?, driver_id = ?, trip_date = ?, departure_time = ?, arrival_time = ?, fare = ?, available_seats = ?, status = ? 
            WHERE trip_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: int, int, int, string, string, string, double, int, string, int -> 'iiisssdisi'
    mysqli_stmt_bind_param($stmt, 'iiisssdisi', $bus_id, $route_id, $driver_id, $trip_date, $departure_time, $arrival_time, $fare, $available_seats, $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_trip($conn, $id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM trips WHERE trip_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Drivers only — for the trip assignment dropdown
function get_drivers()
{
    global $conn;
    $sql = "SELECT user_id, name FROM users WHERE role = 'driver' ORDER BY name ASC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}
