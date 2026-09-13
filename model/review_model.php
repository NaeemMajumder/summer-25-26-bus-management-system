<?php

/*
==========================================================
models/review_model.php
==========================================================
*/

// Confirmed bookings on completed trips that don't have a review yet
function get_completed_bookings_for_review($user_id)
{
    global $conn;

    $sql = "
        SELECT b.booking_id, b.trip_id,
               t.trip_date,
               r.origin, r.destination,
               bs.bus_id, bs.name AS bus_name
        FROM bookings b
        JOIN trips t  ON b.trip_id = t.trip_id
        JOIN routes r ON t.route_id = r.route_id
        JOIN buses bs ON t.bus_id = bs.bus_id
        LEFT JOIN reviews rv ON rv.booking_id = b.booking_id
        WHERE b.user_id = ?
          AND b.booking_status = 'confirmed'
          AND t.status = 'completed'
          AND rv.review_id IS NULL
        ORDER BY t.trip_date DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    return $bookings;
}


function add_review($user_id, $booking_id, $bus_id, $rating, $comment)
{
    global $conn;

    $sql = "INSERT INTO reviews (user_id, booking_id, bus_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiis", $user_id, $booking_id, $bus_id, $rating, $comment);

    return mysqli_stmt_execute($stmt);
}


function get_user_reviews($user_id)
{
    global $conn;

    $sql = "
        SELECT rv.*, b.name AS bus_name, r.origin, r.destination, t.trip_date
        FROM reviews rv
        LEFT JOIN buses b     ON rv.bus_id = b.bus_id
        LEFT JOIN bookings bk ON rv.booking_id = bk.booking_id
        LEFT JOIN trips t     ON bk.trip_id = t.trip_id
        LEFT JOIN routes r    ON t.route_id = r.route_id
        WHERE rv.user_id = ?
        ORDER BY rv.created_at DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }

    return $reviews;
}


function get_review_by_id($review_id, $user_id)
{
    global $conn;

    $sql = "SELECT * FROM reviews WHERE review_id = ? AND user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $review_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}


function update_review($review_id, $user_id, $rating, $comment)
{
    global $conn;

    $sql = "UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $review_id, $user_id);

    return mysqli_stmt_execute($stmt);
}


function delete_review($review_id, $user_id)
{
    global $conn;

    $sql = "DELETE FROM reviews WHERE review_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $review_id, $user_id);

    return mysqli_stmt_execute($stmt);
}


/*
|--------------------------------------------------------------------------
| Unique Feature: Bus Company Rating (aggregated from reviews)
|--------------------------------------------------------------------------
*/

function get_all_bus_ratings()
{
    global $conn;

    $sql = "
        SELECT
            b.bus_id, b.name AS bus_name, b.bus_number, b.type AS bus_type,
            COALESCE(AVG(rv.rating), 0) AS avg_rating,
            COUNT(rv.review_id) AS review_count
        FROM buses b
        LEFT JOIN reviews rv ON rv.bus_id = b.bus_id
        GROUP BY b.bus_id
        ORDER BY avg_rating DESC, review_count DESC
    ";

    $result = mysqli_query($conn, $sql);

    $ratings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ratings[] = $row;
    }

    return $ratings;
}


function get_bus_rating($bus_id)
{
    global $conn;

    $sql = "
        SELECT
            COALESCE(AVG(rating), 0) AS avg_rating,
            COUNT(review_id) AS review_count
        FROM reviews
        WHERE bus_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $bus_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: ['avg_rating' => 0, 'review_count' => 0];
}


// General reviews/comments feed for a bus (shown alongside its rating, if needed)
function get_reviews_for_bus($bus_id, $limit = 10)
{
    global $conn;

    $sql = "
        SELECT rv.*, u.name AS reviewer_name
        FROM reviews rv
        INNER JOIN users u ON rv.user_id = u.user_id
        WHERE rv.bus_id = ?
        ORDER BY rv.created_at DESC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $bus_id, $limit);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }

    return $reviews;
}
