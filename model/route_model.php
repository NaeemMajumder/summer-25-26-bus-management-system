<?php
// models/route_model.php



function get_route_by_id($route_id)
{
    global $conn;

    $sql  = "SELECT * FROM routes WHERE route_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $route_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


// Distinct origins/destinations, used to populate the Search form dropdowns
function get_distinct_origins()
{
    global $conn;

    $sql    = "SELECT DISTINCT origin FROM routes ORDER BY origin ASC";
    $result = mysqli_query($conn, $sql);

    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row['origin'];
    }

    return $list;
}


function get_distinct_destinations()
{
    global $conn;

    $sql    = "SELECT DISTINCT destination FROM routes ORDER BY destination ASC";
    $result = mysqli_query($conn, $sql);

    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row['destination'];
    }

    return $list;
}


// Boarding/dropping points for a route, in order
function get_stops_by_route($route_id)
{
    global $conn;

    $sql  = "SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC, stop_id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $route_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $stops = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stops[] = $row;
    }

    return $stops;
}


function get_stop_by_id($stop_id)
{
    global $conn;

    $sql  = "SELECT * FROM route_stops WHERE stop_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $stop_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}
// ==========================================
// MODEL: routes & route_stops (Admin CRUD module)
// Enforces prepared statements and transactions for 1-to-many integrity
// ==========================================

function get_routes($conn) {
    $sql = "SELECT route_id, origin, destination, distance_km, duration 
            FROM routes ORDER BY route_id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_route($conn, $id) {
    $sql = "SELECT route_id, origin, destination, distance_km, duration 
            FROM routes WHERE route_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function search_routes($conn, $term) {
    $like = '%' . $term . '%';
    $sql = "SELECT route_id, origin, destination, distance_km, duration 
            FROM routes 
            WHERE origin LIKE ? OR destination LIKE ? 
            ORDER BY route_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_route_stops($conn, $route_id) {
    $sql = "SELECT stop_id, stop_name, stop_order 
            FROM route_stops 
            WHERE route_id = ? 
            ORDER BY stop_order ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $route_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function add_route($conn, $origin, $destination, $distance_km, $duration, $stops_array) {
    // Start a transaction to ensure route and stops are created together
    mysqli_begin_transaction($conn);

    try {
        $sql_route = "INSERT INTO routes (origin, destination, distance_km, duration) VALUES (?, ?, ?, ?)";
        $stmt_route = mysqli_prepare($conn, $sql_route);
        mysqli_stmt_bind_param($stmt_route, 'ssds', $origin, $destination, $distance_km, $duration);
        mysqli_stmt_execute($stmt_route);
        
        $route_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_route);

        if (!empty($stops_array)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order) VALUES (?, ?, ?)";
            $stmt_stop = mysqli_prepare($conn, $sql_stop);
            
            $order = 1;
            foreach ($stops_array as $stop_name) {
                $clean_stop = trim($stop_name);
                if ($clean_stop !== '') {
                    mysqli_stmt_bind_param($stmt_stop, 'isi', $route_id, $clean_stop, $order);
                    mysqli_stmt_execute($stmt_stop);
                    $order++;
                }
            }
            mysqli_stmt_close($stmt_stop);
        }

        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

function update_route($conn, $id, $origin, $destination, $distance_km, $duration, $stops_array) {
    mysqli_begin_transaction($conn);

    try {
        $sql_route = "UPDATE routes SET origin = ?, destination = ?, distance_km = ?, duration = ? WHERE route_id = ?";
        $stmt_route = mysqli_prepare($conn, $sql_route);
        mysqli_stmt_bind_param($stmt_route, 'ssdsi', $origin, $destination, $distance_km, $duration, $id);
        mysqli_stmt_execute($stmt_route);
        mysqli_stmt_close($stmt_route);

        // Wipe existing stops and re-insert the fresh list to guarantee perfect synchronization
        $sql_delete = "DELETE FROM route_stops WHERE route_id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, 'i', $id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);

        if (!empty($stops_array)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order) VALUES (?, ?, ?)";
            $stmt_stop = mysqli_prepare($conn, $sql_stop);
            
            $order = 1;
            foreach ($stops_array as $stop_name) {
                $clean_stop = trim($stop_name);
                if ($clean_stop !== '') {
                    mysqli_stmt_bind_param($stmt_stop, 'isi', $id, $clean_stop, $order);
                    mysqli_stmt_execute($stmt_stop);
                    $order++;
                }
            }
            mysqli_stmt_close($stmt_stop);
        }

        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

function delete_route($conn, $id) {
    // Assuming 'ON DELETE CASCADE' is correctly configured on the foreign key in 'route_stops'
    $stmt = mysqli_prepare($conn, "DELETE FROM routes WHERE route_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
