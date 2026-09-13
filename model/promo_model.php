<?php
// models/promo_model.php



function get_promo_by_code($code)
{
    global $conn;

    if (is_blank($code)) {
        return null;
    }

    $sql = "
        SELECT *
        FROM promo_codes
        WHERE code = ?
          AND is_active = 1
          AND (expiry_date IS NULL OR expiry_date >= CURDATE())
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


function get_promo_by_id($promo_id)
{
    global $conn;

    if (empty($promo_id)) {
        return null;
    }

    $sql  = "SELECT * FROM promo_codes WHERE promo_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $promo_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}
// ==========================================
// MODEL: promo_codes (Admin CRUD module)
// Enforces prepared statements for all queries
// ==========================================

function get_promos($conn) {
    $sql = "SELECT promo_id, code, discount_type, discount_value, expiry_date, is_active 
            FROM promo_codes ORDER BY promo_id DESC";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_promo($conn, $id) {
    $sql = "SELECT promo_id, code, discount_type, discount_value, expiry_date, is_active 
            FROM promo_codes WHERE promo_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function search_promos($conn, $term) {
    $like = '%' . $term . '%';
    $sql = "SELECT promo_id, code, discount_type, discount_value, expiry_date, is_active 
            FROM promo_codes 
            WHERE code LIKE ? OR discount_type LIKE ? 
            ORDER BY promo_id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function promo_code_exists($conn, $code, $excludeId = 0) {
    $sql = "SELECT promo_id FROM promo_codes WHERE code = ? AND promo_id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $code, $excludeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function add_promo($conn, $code, $discount_type, $discount_value, $expiry_date, $is_active) {
    $sql = "INSERT INTO promo_codes (code, discount_type, discount_value, expiry_date, is_active) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: string, string, double/decimal, string (date), integer (tinyint) -> 'ssdsi'
    mysqli_stmt_bind_param($stmt, 'ssdsi', $code, $discount_type, $discount_value, $expiry_date, $is_active);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_promo($conn, $id, $code, $discount_type, $discount_value, $expiry_date, $is_active) {
    $sql = "UPDATE promo_codes 
            SET code = ?, discount_type = ?, discount_value = ?, expiry_date = ?, is_active = ? 
            WHERE promo_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    // Types: string, string, double/decimal, string, integer, integer -> 'ssdsii'
    mysqli_stmt_bind_param($stmt, 'ssdsii', $code, $discount_type, $discount_value, $expiry_date, $is_active, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_promo($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM promo_codes WHERE promo_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


