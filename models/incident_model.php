<?php
// ==========================================
// MODEL: incident_reports (Admin Unique Feature: Damage Report)
// Admin views 'damage' type reports and marks them as resolved.
// ==========================================

function get_damage_reports($conn)
{
    // LEFT JOIN is used for buses and trips since they are nullable in the schema
    $sql = "SELECT i.incident_id, i.description, i.status, i.created_at,
                   u.name AS driver_name, 
                   b.bus_number, 
                   t.trip_date
            FROM incident_reports i
            JOIN users u ON i.driver_id = u.user_id
            LEFT JOIN buses b ON i.bus_id = b.bus_id
            LEFT JOIN trips t ON i.trip_id = t.trip_id
            WHERE i.type = 'damage'
            ORDER BY FIELD(i.status, 'open', 'reviewing', 'resolved'), i.incident_id DESC";

    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function search_damage_reports($conn, $term)
{
    $like = '%' . $term . '%';
    $sql = "SELECT i.incident_id, i.description, i.status, i.created_at,
                   u.name AS driver_name, 
                   b.bus_number, 
                   t.trip_date
            FROM incident_reports i
            JOIN users u ON i.driver_id = u.user_id
            LEFT JOIN buses b ON i.bus_id = b.bus_id
            LEFT JOIN trips t ON i.trip_id = t.trip_id
            WHERE i.type = 'damage' 
              AND (i.description LIKE ? OR u.name LIKE ? OR b.bus_number LIKE ?)
            ORDER BY FIELD(i.status, 'open', 'reviewing', 'resolved'), i.incident_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $rows;
}

function get_incident($conn, $id)
{
    $sql = "SELECT incident_id, driver_id, trip_id, bus_id, type, description, status, created_at 
            FROM incident_reports WHERE incident_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function resolve_damage_report($conn, $id)
{
    // Ensures the admin can only resolve reports that are specifically marked as 'damage'
    $sql = "UPDATE incident_reports 
            SET status = 'resolved' 
            WHERE incident_id = ? AND type = 'damage' AND status != 'resolved'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    $changed = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $ok && $changed;

}
// ==========================================
// MODEL: incident_reports (Admin Unique Feature: Damage Report finished)
// Admin views 'damage' type reports and marks them as resolved.
// ==========================================


// ==========================================
// MODEL: incident_reports (Driver CRUD + Unique Feature)
// A driver files/manages their own incident & damage reports.
// ==========================================

// All reports filed by this driver (both types), newest first
function get_driver_incidents($conn, $driver_id)
{
    $sql = "SELECT i.incident_id, i.trip_id, i.bus_id, i.type, i.description, i.status, i.created_at,
                   b.bus_number,
                   r.origin, r.destination
            FROM incident_reports i
            LEFT JOIN buses b  ON i.bus_id = b.bus_id
            LEFT JOIN trips t  ON i.trip_id = t.trip_id
            LEFT JOIN routes r ON t.route_id = r.route_id
            WHERE i.driver_id = ?
            ORDER BY i.incident_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $driver_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}


// One report (ownership-checked with driver_id) — for edit-prefill and delete guard
function get_driver_incident($conn, $incident_id, $driver_id)
{
    $sql = "SELECT * FROM incident_reports WHERE incident_id = ? AND driver_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $incident_id, $driver_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}


// Looks up which bus a trip uses — so the driver doesn't have to pick a bus separately
function get_bus_id_for_trip($conn, $trip_id)
{
    $sql = "SELECT bus_id FROM trips WHERE trip_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $trip_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ? (int) $row['bus_id'] : null;
}


// New report always starts 'open' — admin moves it through reviewing -> resolved
function add_incident($conn, $driver_id, $trip_id, $bus_id, $type, $description)
{
    $sql = "INSERT INTO incident_reports (driver_id, trip_id, bus_id, type, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'open', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iiiss', $driver_id, $trip_id, $bus_id, $type, $description);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


// Ownership-checked — driver can only edit their own report's content.
// status is intentionally left out here: that's the admin's job (Damage Report -> Resolve).
function update_incident($conn, $incident_id, $driver_id, $trip_id, $bus_id, $type, $description)
{
    $sql = "UPDATE incident_reports
            SET trip_id = ?, bus_id = ?, type = ?, description = ?
            WHERE incident_id = ? AND driver_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iissii', $trip_id, $bus_id, $type, $description, $incident_id, $driver_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


// Ownership-checked in the WHERE — same guard as above
function delete_incident($conn, $incident_id, $driver_id)
{
    $sql = "DELETE FROM incident_reports WHERE incident_id = ? AND driver_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $incident_id, $driver_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}