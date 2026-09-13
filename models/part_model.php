<?php
// models/part_model.php

// All spare parts, alphabetical by name
function get_all_parts($conn)
{
    $sql = "SELECT * FROM spare_parts ORDER BY part_name ASC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function search_parts($conn, $term)
{
    $like = '%' . $term . '%';
    $sql = "SELECT * FROM spare_parts WHERE part_name LIKE ? ORDER BY part_name ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_part($conn, $part_id)
{
    $sql = "SELECT * FROM spare_parts WHERE part_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $part_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function add_part($conn, $part_name, $stock_quantity, $unit_price)
{
    $sql = "INSERT INTO spare_parts (part_name, stock_quantity, unit_price, updated_at)
            VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sid', $part_name, $stock_quantity, $unit_price);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_part($conn, $part_id, $part_name, $stock_quantity, $unit_price)
{
    $sql = "UPDATE spare_parts
            SET part_name = ?, stock_quantity = ?, unit_price = ?, updated_at = NOW()
            WHERE part_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sidi', $part_name, $stock_quantity, $unit_price, $part_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_part($conn, $part_id)
{
    $sql = "DELETE FROM spare_parts WHERE part_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $part_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Parts at or below the low-stock threshold — for the Manager dashboard alert
function get_low_stock_parts($conn)
{
    $threshold = PARTS_LOW_STOCK;
    $sql = "SELECT * FROM spare_parts WHERE stock_quantity <= ? ORDER BY stock_quantity ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $threshold);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}