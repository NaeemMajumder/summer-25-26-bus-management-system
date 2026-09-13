<?php
// ==========================================
// MODEL: users
// Auth, profile, password, reset + admin oversight
// All queries use prepared statements.
// ==========================================


/* ---------- AUTH ---------- */

// Log a user in: find by email, verify hashed password.
// Returns the user row on success, or false.
function check_login($email, $password) {
    global $conn;
    $sql  = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Create a new user (registration). Password is hashed here.
// Role-specific fields are optional and default to null.
// Returns the new user_id, or false.
function create_user($name, $email, $phone, $password, $role = 'passenger',
                     $nid = null, $license = null, $experience = null, $prevCompany = null) {
    global $conn;
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users
              (name, email, phone, password, role,
               nid_number, license_number, experience_years, previous_company, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);

    // types: name s, email s, phone s, pass s, role s,
    // nid s, license s, experience i, prevCompany s  -> 'sssssssis'
    mysqli_stmt_bind_param(
        $stmt,
        'sssssssis',
        $name, $email, $phone, $hash, $role,
        $nid, $license, $experience, $prevCompany
    );

    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

/* ---------- LOOKUPS ---------- */

function find_user_by_id($id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function find_user_by_email($email) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// Used at registration to block duplicate email OR phone.
function find_user_by_email_or_phone($email, $phone) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ss', $email, $phone);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}


/* ---------- PROFILE ---------- */

function update_profile($id, $name, $phone) {
    global $conn;
    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $phone, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Check a user's current password (for change-password / delete-account).
function verify_password($id, $password) {
    $user = find_user_by_id($id);
    return $user && password_verify($password, $user['password']);
}

function update_password($id, $newPassword) {
    global $conn;
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_account($id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


/* ---------- FORGOT / RESET PASSWORD ---------- */

function set_reset_token($email, $token) {
    global $conn;
    $stmt = mysqli_prepare($conn, "UPDATE users SET reset_token = ? WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $token, $email);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function reset_password_with_token($token, $newPassword) {
    global $conn;
    // find the user holding this token
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE reset_token = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) {
        return false;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $row['user_id']);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


/* ---------- ADMIN OVERSIGHT (user list + ban/suspend) ---------- */

function get_users($conn, $role = '') {
    if ($role === '') {
        $sql = "SELECT user_id, name, email, phone, role, created_at
                FROM users ORDER BY user_id DESC";
        $res = mysqli_query($conn, $sql);
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    } else {
        $sql = "SELECT user_id, name, email, phone, role, created_at
                FROM users WHERE role = ? ORDER BY user_id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $role);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $rows;
    }
}

function search_users($conn, $term, $role = '') {
    $like = '%' . $term . '%';
    if ($role === '') {
        $sql = "SELECT user_id, name, email, phone, role, created_at
                FROM users
                WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?
                ORDER BY user_id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    } else {
        $sql = "SELECT user_id, name, email, phone, role, created_at
                FROM users
                WHERE role = ? AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)
                ORDER BY user_id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $role, $like, $like, $like);
    }
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function set_user_status($conn, $id, $status) {
    $sql = "UPDATE users SET status = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}