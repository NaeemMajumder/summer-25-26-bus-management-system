<?php
// models/availability_model.php

// All availability entries for a driver, newest date first
function get_driver_availability($conn, $driver_id)
{
    $sql = "SELECT * FROM driver_availability WHERE driver_id = ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $driver_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}


// One entry (ownership-checked with driver_id) — used for edit-prefill
function get_availability($conn, $availability_id, $driver_id)
{
    $sql = "SELECT * FROM driver_availability WHERE availability_id = ? AND driver_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $availability_id, $driver_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}


function add_availability($conn, $driver_id, $date, $status, $note = '')
{
    $sql = "INSERT INTO driver_availability (driver_id, date, status, note) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isss', $driver_id, $date, $status, $note);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


// Ownership-checked in the WHERE — a driver can only ever update their own row
function update_availability($conn, $availability_id, $driver_id, $date, $status, $note = '')
{
    $sql = "UPDATE driver_availability
            SET date = ?, status = ?, note = ?
            WHERE availability_id = ? AND driver_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssii', $date, $status, $note, $availability_id, $driver_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


// Ownership-checked in the WHERE — same guard as above
function delete_availability($conn, $availability_id, $driver_id)
{
    $sql = "DELETE FROM driver_availability WHERE availability_id = ? AND driver_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $availability_id, $driver_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}