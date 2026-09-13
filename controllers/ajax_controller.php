<?php
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/route_model.php';
require_once __DIR__ . '/../models/trip_model.php';
require_once __DIR__ . '/../models/promo_model.php';

$action = $_GET['action'] ?? '';


/*
search_trips&from=&to=&date=&type=&sort=
*/
if ($action === 'search_trips') {

    $from = clean_input($_GET['from'] ?? '');
    $to = clean_input($_GET['to'] ?? '');
    $date = clean_input($_GET['date'] ?? '');
    $type = clean_input($_GET['type'] ?? '');
    $sort = clean_input($_GET['sort'] ?? 'departure');

    // All 3 are now optional — a blank value just means "don't filter on this".
    if (!is_blank($date) && !valid_date($date)) {
        json_out(['status' => 'error', 'message' => 'Please choose a valid travel date.']);
    }
    if (!is_blank($from) && !is_blank($to) && $from === $to) {
        json_out(['status' => 'error', 'message' => 'Origin and destination cannot be the same.']);
    }

    if (!in_array($type, ['', 'AC', 'Non-AC'], true)) {
        $type = '';
    }

    $trips = search_trips($from, $to, $date, $type, $sort);

    $data = array_map(function ($t) {
        return [
            'trip_id' => (int) $t['trip_id'],
            'bus_name' => $t['bus_name'],
            'bus_number' => $t['bus_number'],
            'bus_type' => $t['bus_type'],
            'origin' => $t['origin'],
            'destination' => $t['destination'],
            'departure_time' => $t['departure_time'],
            'arrival_time' => $t['arrival_time'],
            'duration' => $t['duration'],
            'fare' => (float) $t['fare'],
            'fare_display' => format_currency($t['fare']),
            'available_seats' => (int) $t['available_seats'],
            'avg_rating' => round((float) $t['avg_rating'], 1),
            'rating_count' => (int) $t['review_count'],
        ];
    }, $trips);

    json_out(['status' => 'success', 'data' => $data]);
}
/*
trip_details&id=
*/
if ($action === 'trip_details') {

    $tripId = (int) ($_GET['id'] ?? 0);
    $trip = get_trip_details($tripId);

    if (!$trip) {
        json_out(['status' => 'error', 'message' => 'Trip not found.'], 404);
    }
    json_out(['status' => 'success', 'data' => $trip]);
}
/*
route_stops&route=
*/
if ($action === 'route_stops') {

    $routeId = (int) ($_GET['route'] ?? 0);
    $stops = get_stops_by_route($routeId);

    json_out(['status' => 'success', 'data' => $stops]);
    exit;
}
/*
 fare_calc&trip_id=&seats=&promo_code=
*/

if ($action === 'fare_calc') {

    $tripId = (int) ($_GET['trip_id'] ?? 0);
    $seats = (int) ($_GET['seats'] ?? 1);
    $promoCode = clean_input($_GET['promo_code'] ?? '');

    $trip = get_trip_basic($tripId);

    if (!$trip) {
        json_out(['status' => 'error', 'message' => 'Trip not found.'], 404);
    }

    if ($seats < 1 || $seats > SEATS_PER_BOOKING) {
        json_out(['status' => 'error', 'message' => 'You can book between 1 and ' . SEATS_PER_BOOKING . ' seats.']);
    }

    if ($seats > (int) $trip['available_seats']) {
        json_out(['status' => 'error', 'message' => 'Only ' . $trip['available_seats'] . ' seat(s) left.']);
        exit;
    }


    $promo = null;
    $promoValid = null;
    $promoNote = '';

    if (!is_blank($promoCode)) {
        $promo = get_promo_by_code($promoCode);

        if ($promo) {
            $promoValid = true;
            $promoNote = 'Promo applied!';
        } else {
            $promoValid = false;
            $promoNote = 'Invalid or expired promo code.';
        }
    }

    $totals = calculate_total($trip['fare'], $seats, $promo);

    json_out([
        'status' => 'success',
        'fare' => (float) $trip['fare'],
        'seats' => $seats,
        'subtotal' => $totals['subtotal'],
        'discount' => $totals['discount'],
        'total' => $totals['total'],
        'total_display' => format_currency($totals['total']),
        'promo_valid' => $promoValid,
        'promo_note' => $promoNote,
    ]);
}
/*
verify&code=
Driver-only: looks up a ticket code and confirms it belongs to one of this driver's trips.
*/
if ($action === 'verify') {

    require_once __DIR__ . '/../models/booking_model.php';

    // No require_role() here on purpose — that helper redirects on failure,
    // which breaks a fetch() call expecting JSON. We check manually instead.
    if (!is_logged_in() || current_role() !== 'driver') {
        json_out(['status' => 'error', 'message' => 'Not authorised.'], 403);
    }

    $code = clean_input($_GET['code'] ?? '');

    if (!preg_match('/^GNT-(\d+)$/i', $code, $matches)) {
        json_out(['status' => 'error', 'message' => 'Invalid ticket code format.']);
    }

    $bookingId = (int) $matches[1];
    $booking = get_booking_for_verification($bookingId);

    if (!$booking) {
        json_out(['status' => 'error', 'message' => 'Ticket not found.']);
    }

    if ((int) $booking['driver_id'] !== current_user_id()) {
        json_out(['status' => 'error', 'message' => 'This ticket is not for one of your trips.']);
    }

    if ($booking['booking_status'] !== 'confirmed') {
        json_out(['status' => 'error', 'message' => 'This ticket has been cancelled.']);
    }

    json_out([
        'status' => 'success',
        'data' => [
            'booking_id' => (int) $booking['booking_id'],
            'passenger_name' => $booking['passenger_name'],
            'seats_booked' => (int) $booking['seats_booked'],
            'route' => $booking['origin'] . ' -> ' . $booking['destination'],
            'trip_date' => $booking['trip_date'],
            'boarding_stop' => $booking['boarding_stop'] ?? '-',
            'dropping_stop' => $booking['dropping_stop'] ?? '-',
            'wheelchair' => (bool) $booking['wheelchair'],
            'payment_status' => $booking['payment_status'],
        ],
    ]);
}
/*
 Unknown action
*/

json_out(['status' => 'error', 'message' => 'Unknown AJAX action.'], 400);