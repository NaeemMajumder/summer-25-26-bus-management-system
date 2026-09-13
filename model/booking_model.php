<?php
// ==========================================
// MODEL: bookings (Admin Unique Feature: Revenue Report)
// Computes SUM(total_amount) for 'paid' bookings with date filters for charting
// ==========================================

function get_total_revenue($conn, $start_date = null, $end_date = null)
{
    $sql = "SELECT SUM(total_amount) AS total_revenue 
            FROM bookings 
            WHERE payment_status = 'paid' AND booking_status = 'confirmed'";

    if ($start_date && $end_date) {
        $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
    } else {
        $stmt = mysqli_prepare($conn, $sql);
    }

    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Return 0.00 if there is no revenue to prevent null outputs in the view
    return (float) ($row['total_revenue'] ?? 0.00);
}

function get_revenue_chart_data($conn, $start_date = null, $end_date = null)
{
    $sql = "SELECT DATE(created_at) AS revenue_date, SUM(total_amount) AS daily_revenue 
            FROM bookings 
            WHERE payment_status = 'paid' AND booking_status = 'confirmed'";

    if ($start_date && $end_date) {
        $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
    }

    $sql .= " GROUP BY DATE(created_at) ORDER BY revenue_date ASC";

    if ($start_date && $end_date) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
    } else {
        $stmt = mysqli_prepare($conn, $sql);
    }

    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $rows;
}

// ==========================================
// MODEL: bookings (Admin Unique Feature: Revenue Report finished)
// Computes SUM(total_amount) for 'paid' bookings with date filters for charting
// ==========================================


/* ---------- Passenger dashboard helpers ---------- */

function get_recent_bookings($user_id, $limit = 5)
{
    global $conn;
    $sql = "SELECT b.booking_id, b.seats_booked, b.total_amount, b.booking_status,
                   b.payment_status, b.created_at,
                   t.trip_date, t.departure_time,
                   r.origin, r.destination,
                   bs.name AS bus_name
            FROM bookings b
            JOIN trips t   ON b.trip_id = t.trip_id
            JOIN routes r  ON t.route_id = r.route_id
            JOIN buses bs  ON t.bus_id = bs.bus_id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function get_booking_summary($user_id)
{
    global $conn;
    $sql = "SELECT
                COUNT(*) AS total,
                SUM(booking_status = 'confirmed') AS confirmed,
                SUM(payment_status = 'pending')   AS pending,
                SUM(booking_status = 'cancelled') AS cancelled
            FROM bookings
            WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return [
        'total' => (int) ($row['total'] ?? 0),
        'confirmed' => (int) ($row['confirmed'] ?? 0),
        'pending' => (int) ($row['pending'] ?? 0),
        'cancelled' => (int) ($row['cancelled'] ?? 0),
    ];
}


/* ---------- Driver: Verify Passenger (ticket lookup) ---------- */

function get_booking_for_verification($booking_id)
{
    global $conn;
    $sql = "SELECT b.booking_id, b.seats_booked, b.wheelchair, b.payment_status, b.booking_status,
                   t.trip_id, t.trip_date, t.driver_id,
                   u.name AS passenger_name,
                   r.origin, r.destination,
                   bo.stop_name AS boarding_stop,
                   dr.stop_name AS dropping_stop
            FROM bookings b
            JOIN trips t              ON b.trip_id = t.trip_id
            JOIN users u              ON b.user_id = u.user_id
            JOIN routes r             ON t.route_id = r.route_id
            LEFT JOIN route_stops bo  ON b.boarding_stop_id = bo.stop_id
            LEFT JOIN route_stops dr  ON b.dropping_stop_id = dr.stop_id
            WHERE b.booking_id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}


/* ---------- Booking CRUD (Passenger) ---------- */

// Creates the booking (status: confirmed / payment: pending) and returns the new booking_id, or false
function create_booking($user_id, $trip_id, $seats, $boarding_stop_id, $dropping_stop_id, $wheelchair, $promo_id, $total_amount, $payment_method)
{
    global $conn;
    $sql = "INSERT INTO bookings
                (user_id, trip_id, seats_booked, boarding_stop_id, dropping_stop_id,
                 wheelchair, promo_id, total_amount, payment_method, payment_status,
                 booking_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'confirmed', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'iiiiiiids',
        $user_id,
        $trip_id,
        $seats,
        $boarding_stop_id,
        $dropping_stop_id,
        $wheelchair,
        $promo_id,
        $total_amount,
        $payment_method
    );
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

// This user's bookings, optionally filtered by a search term and/or booking_status
function get_user_bookings($user_id, $search = '', $status = '')
{
    global $conn;
    $sql = "SELECT b.booking_id, b.seats_booked, b.wheelchair, b.total_amount,
                   b.payment_method, b.payment_status, b.booking_status, b.created_at,
                   t.trip_id, t.trip_date, t.departure_time, t.status AS trip_status,
                   r.origin, r.destination,
                   bs.name AS bus_name, bs.bus_number
            FROM bookings b
            JOIN trips t  ON b.trip_id = t.trip_id
            JOIN routes r ON t.route_id = r.route_id
            JOIN buses bs ON t.bus_id = bs.bus_id
            WHERE b.user_id = ?";

    $types = 'i';
    $params = [$user_id];

    if (!is_blank($search)) {
        $like = '%' . $search . '%';
        $sql .= " AND (r.origin LIKE ? OR r.destination LIKE ? OR bs.name LIKE ?)";
        $types .= 'sss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (!is_blank($status)) {
        $sql .= " AND b.booking_status = ?";
        $types .= 's';
        $params[] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

// One booking (ownership-checked) with everything the Ticket/Modify pages need
function get_booking_by_id($booking_id, $user_id)
{
    global $conn;
    $sql = "SELECT b.*,
                   t.trip_date, t.departure_time, t.arrival_time, t.fare, t.route_id,
                   t.status AS trip_status,
                   r.origin, r.destination,
                   bs.name AS bus_name, bs.bus_number, bs.type AS bus_type,
                   bo.stop_name AS boarding_stop_name,
                   dr.stop_name AS dropping_stop_name
            FROM bookings b
            JOIN trips t   ON b.trip_id = t.trip_id
            JOIN routes r  ON t.route_id = r.route_id
            JOIN buses bs  ON t.bus_id = bs.bus_id
            LEFT JOIN route_stops bo ON b.boarding_stop_id = bo.stop_id
            LEFT JOIN route_stops dr ON b.dropping_stop_id = dr.stop_id
            WHERE b.booking_id = ? AND b.user_id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// Changes seats/boarding/dropping/wheelchair on a confirmed booking.
// If seat count changes, the trip's available_seats is adjusted to match —
// atomically, so it can't oversell if someone else just booked in the meantime.
function modify_booking($booking_id, $user_id, $boarding_stop_id, $dropping_stop_id, $new_seats, $wheelchair)
{
    global $conn;

    $booking = get_booking_by_id($booking_id, $user_id);
    if (!$booking || $booking['booking_status'] !== 'confirmed') {
        return false;
    }

    $trip_id = (int) $booking['trip_id'];
    $old_seats = (int) $booking['seats_booked'];
    $diff = $new_seats - $old_seats;

    if ($diff > 0) {
        // Needs more seats than before — reserve the extra atomically
        $sql = "UPDATE trips SET available_seats = available_seats - ? WHERE trip_id = ? AND available_seats >= ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $diff, $trip_id, $diff);
        mysqli_stmt_execute($stmt);
        $reserved = mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$reserved) {
            return false; // not enough seats left on the trip
        }
    } elseif ($diff < 0) {
        // Frees up seats
        $freed = -$diff;
        $sql = "UPDATE trips SET available_seats = available_seats + ? WHERE trip_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $freed, $trip_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $sql = "UPDATE bookings
            SET boarding_stop_id = ?, dropping_stop_id = ?, seats_booked = ?, wheelchair = ?
            WHERE booking_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iiiiii', $boarding_stop_id, $dropping_stop_id, $new_seats, $wheelchair, $booking_id, $user_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

// Cancels a confirmed booking and gives its seats back to the trip
function cancel_booking($booking_id, $user_id)
{
    global $conn;

    $booking = get_booking_by_id($booking_id, $user_id);
    if (!$booking || $booking['booking_status'] !== 'confirmed') {
        return false;
    }

    $sql = "UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $seats = (int) $booking['seats_booked'];
        $trip_id = (int) $booking['trip_id'];

        $sql = "UPDATE trips SET available_seats = available_seats + ? WHERE trip_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $seats, $trip_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    return $ok;
}

// Updates payment method and/or marks a booking as paid (ownership-checked)
function update_payment($booking_id, $user_id, $method, $mark_paid)
{
    global $conn;

    $booking = get_booking_by_id($booking_id, $user_id);
    if (!$booking) {
        return false;
    }

    $newMethod = $method ?: $booking['payment_method'];
    $newStatus = $mark_paid ? 'paid' : $booking['payment_status'];

    $sql = "UPDATE bookings SET payment_method = ?, payment_status = ? WHERE booking_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssii', $newMethod, $newStatus, $booking_id, $user_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}