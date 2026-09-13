<?php
// models/bus_model.php



function get_bus_by_id($bus_id)
{
    global $conn;

    $sql = "SELECT * FROM buses WHERE bus_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $bus_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


function get_active_buses()
{
    global $conn;

    $sql = "SELECT * FROM buses WHERE status = 'active' ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);

    $buses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $buses[] = $row;
    }

    return $buses;
}
// ==========================================
// MODEL: buses (Admin CRUD module)
// Enforces prepared statements for all queries
// ==========================================

function get_buses($conn)
{
    $sql = "SELECT bus_id, bus_number, name, type, total_seats, status, service_trip_limit, trips_since_service 
            FROM buses ORDER BY bus_id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_bus($conn, $id)
{
    $sql = "SELECT bus_id, bus_number, name, type, total_seats, status, service_trip_limit, trips_since_service 
            FROM buses WHERE bus_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function search_buses($conn, $term)
{
    $like = '%' . $term . '%';
    $sql = "SELECT bus_id, bus_number, name, type, total_seats, status, service_trip_limit, trips_since_service 
            FROM buses 
            WHERE bus_number LIKE ? OR name LIKE ? OR type LIKE ? OR status LIKE ? 
            ORDER BY bus_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function bus_number_exists($conn, $bus_number, $excludeId = 0)
{
    $sql = "SELECT bus_id FROM buses WHERE bus_number = ? AND bus_id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $bus_number, $excludeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function add_bus($conn, $bus_number, $name, $type, $total_seats, $status, $service_trip_limit)
{
    $sql = "INSERT INTO buses (bus_number, name, type, total_seats, status, service_trip_limit, trips_since_service) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: string, string, string, integer, string, integer -> 'ssisii'
     mysqli_stmt_bind_param($stmt, 'sssisi', $bus_number, $name, $type, $total_seats, $status, $service_trip_limit);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_bus($conn, $id, $bus_number, $name, $type, $total_seats, $status, $service_trip_limit)
{
    $sql = "UPDATE buses 
            SET bus_number = ?, name = ?, type = ?, total_seats = ?, status = ?, service_trip_limit = ? 
            WHERE bus_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: string, string, string, integer, string, integer, integer -> 'sssisii'
    mysqli_stmt_bind_param($stmt, 'sssisii', $bus_number, $name, $type, $total_seats, $status, $service_trip_limit, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_bus($conn, $id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM buses WHERE bus_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
