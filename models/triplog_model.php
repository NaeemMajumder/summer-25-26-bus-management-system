<?php
// models/triplog_model.php

// All trips assigned to a driver — pass $date to filter to one day (e.g. today's dashboard),
// or omit it to get every assigned trip, newest first.
function get_driver_trips($conn, $driver_id, $date = null)
{
    if ($date) {
        $sql = "SELECT t.trip_id, t.trip_date, t.departure_time, t.arrival_time,
                       t.fare, t.available_seats, t.status,
                       b.name AS bus_name, b.bus_number,
                       r.origin, r.destination
                FROM trips t
                JOIN buses b  ON t.bus_id = b.bus_id
                JOIN routes r ON t.route_id = r.route_id
                WHERE t.driver_id = ? AND t.trip_date = ?
                ORDER BY t.departure_time ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $driver_id, $date);
    } else {
        $sql = "SELECT t.trip_id, t.trip_date, t.departure_time, t.arrival_time,
                       t.fare, t.available_seats, t.status,
                       b.name AS bus_name, b.bus_number,
                       r.origin, r.destination
                FROM trips t
                JOIN buses b  ON t.bus_id = b.bus_id
                JOIN routes r ON t.route_id = r.route_id
                WHERE t.driver_id = ?
                ORDER BY t.trip_date DESC, t.departure_time ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $driver_id);
    }
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}


// A driver's trip logs (with trip/route info) — for the Trip Logs page
function get_driver_logs($conn, $driver_id)
{
    $sql = "SELECT l.log_id, l.trip_id, l.status, l.start_time, l.end_time, l.note,
                   r.origin, r.destination, t.trip_date
            FROM trip_logs l
            JOIN trips t  ON l.trip_id = t.trip_id
            JOIN routes r ON t.route_id = r.route_id
            WHERE l.driver_id = ?
            ORDER BY l.log_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $driver_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}


// One log (ownership-checked with driver_id)
function get_log($conn, $log_id, $driver_id)
{
    $sql = "SELECT * FROM trip_logs WHERE log_id = ? AND driver_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $log_id, $driver_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}


// Is this trip actually assigned to this driver? (guard before starting a log)
function trip_belongs_to_driver($conn, $trip_id, $driver_id)
{
    $sql = "SELECT trip_id FROM trips WHERE trip_id = ? AND driver_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $trip_id, $driver_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $ok = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
}


// Start a trip: create a log (status=started) + set trip status to running
function start_trip_log($conn, $trip_id, $driver_id, $note = '')
{
    $sql = "INSERT INTO trip_logs (trip_id, driver_id, status, start_time, note)
            VALUES (?, ?, 'started', NOW(), ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iis', $trip_id, $driver_id, $note);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $u = mysqli_prepare($conn, "UPDATE trips SET status = 'running' WHERE trip_id = ?");
        mysqli_stmt_bind_param($u, 'i', $trip_id);
        mysqli_stmt_execute($u);
        mysqli_stmt_close($u);
    }
    return $ok;
}


// Complete a trip: log end_time + status=completed, trip=completed, bump service counter
function complete_trip_log($conn, $log_id, $driver_id)
{
    $log = get_log($conn, $log_id, $driver_id);
    if (!$log) {
        return false;
    }

    $stmt = mysqli_prepare($conn, "UPDATE trip_logs SET status = 'completed', end_time = NOW() WHERE log_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $log_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $t = mysqli_prepare($conn, "UPDATE trips SET status = 'completed' WHERE trip_id = ?");
        mysqli_stmt_bind_param($t, 'i', $log['trip_id']);
        mysqli_stmt_execute($t);
        mysqli_stmt_close($t);

        $b = mysqli_prepare($conn, "UPDATE buses b
                                    JOIN trips t ON t.bus_id = b.bus_id
                                    SET b.trips_since_service = b.trips_since_service + 1
                                    WHERE t.trip_id = ?");
        mysqli_stmt_bind_param($b, 'i', $log['trip_id']);
        mysqli_stmt_execute($b);
        mysqli_stmt_close($b);
    }
    return $ok;
}


function delete_log($conn, $log_id, $driver_id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM trip_logs WHERE log_id = ? AND driver_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $log_id, $driver_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}