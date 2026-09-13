<?php
// models/feedback_model.php



// $user_id may be null — guests can also leave feedback (schema allows it)
function add_feedback($user_id, $message)
{
    global $conn;

    $sql  = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (empty($user_id)) {
        $null = null;
        mysqli_stmt_bind_param($stmt, "is", $null, $message);
    } else {
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
    }

    return mysqli_stmt_execute($stmt);
}
// ==========================================
// MODEL: feedback (Admin Unique Feature)
// Read-only module for Admin to view system feedback
// ==========================================

function get_all_feedback($conn) {
    // Uses a LEFT JOIN because user_id is nullable in the schema
    $sql = "SELECT f.feedback_id, f.message, f.created_at, 
                   u.name AS user_name, u.email AS user_email, u.role AS user_role
            FROM feedback f
            LEFT JOIN users u ON f.user_id = u.user_id
            ORDER BY f.feedback_id DESC";
    
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function search_feedback($conn, $term) {
    $like = '%' . $term . '%';
    $sql = "SELECT f.feedback_id, f.message, f.created_at, 
                   u.name AS user_name, u.email AS user_email, u.role AS user_role
            FROM feedback f
            LEFT JOIN users u ON f.user_id = u.user_id
            WHERE f.message LIKE ? OR u.name LIKE ? OR u.email LIKE ?
            ORDER BY f.feedback_id DESC";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    
    return $rows;
}
