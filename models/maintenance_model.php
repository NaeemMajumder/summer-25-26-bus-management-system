<?php
// models/maintenance_model.php

// All requests filed by this manager, most-urgent status first
function get_manager_requests($conn, $manager_id)
{
    $sql = "SELECT r.request_id, r.bus_id, r.issue, r.status, r.created_at,
                   b.name AS bus_name, b.bus_number
            FROM maintenance_requests r
            JOIN buses b ON r.bus_id = b.bus_id
            WHERE r.manager_id = ?
            ORDER BY FIELD(r.status, 'pending', 'in_progress', 'done', 'cancelled'), r.request_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $manager_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function search_manager_requests($conn, $manager_id, $term)
{
    $like = '%' . $term . '%';
    $sql = "SELECT r.request_id, r.bus_id, r.issue, r.status, r.created_at,
                   b.name AS bus_name, b.bus_number
            FROM maintenance_requests r
            JOIN buses b ON r.bus_id = b.bus_id
            WHERE r.manager_id = ? AND (r.issue LIKE ? OR b.bus_number LIKE ?)
            ORDER BY FIELD(r.status, 'pending', 'in_progress', 'done', 'cancelled'), r.request_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iss', $manager_id, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

// Simple bus list for the "which bus" dropdown — kept local here so this
// model doesn't depend on whatever function names bus_model.php happens to use.
function get_buses_for_dropdown($conn)
{
    $sql = "SELECT bus_id, name, bus_number FROM buses ORDER BY name ASC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function add_request($conn, $bus_id, $manager_id, $issue)
{
    $sql = "INSERT INTO maintenance_requests (bus_id, manager_id, issue, status, created_at)
            VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iis', $bus_id, $manager_id, $issue);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Moves the request through pending -> in_progress -> done (ownership-checked)
function update_request_status($conn, $request_id, $manager_id, $status)
{
    $sql = "UPDATE maintenance_requests SET status = ? WHERE request_id = ? AND manager_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $status, $request_id, $manager_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Separate from update_request_status so "cancel" is its own explicit endpoint
// (matches the Technical Doc's dedicated cancelrequest&id action)
function cancel_request($conn, $request_id, $manager_id)
{
    $sql = "UPDATE maintenance_requests SET status = 'cancelled' WHERE request_id = ? AND manager_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $request_id, $manager_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Buses that have hit or passed their service trip limit — for the Manager dashboard alert
function get_buses_needing_service($conn)
{
    $sql = "SELECT bus_id, name, bus_number, trips_since_service, service_trip_limit
            FROM buses
            WHERE trips_since_service >= service_trip_limit
            ORDER BY trips_since_service DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

// This manager's still-open requests (pending or in_progress) — dashboard summary
function get_open_requests($conn, $manager_id)
{
    $sql = "SELECT r.request_id, r.issue, r.status, r.created_at,
                   b.name AS bus_name, b.bus_number
            FROM maintenance_requests r
            JOIN buses b ON r.bus_id = b.bus_id
            WHERE r.manager_id = ? AND r.status IN ('pending', 'in_progress')
            ORDER BY FIELD(r.status, 'pending', 'in_progress'), r.request_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $manager_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}