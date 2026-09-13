<?php
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/bus_model.php';
require_once __DIR__ . '/../models/route_model.php';
require_once __DIR__ . '/../models/trip_model.php';
require_once __DIR__ . '/../models/booking_model.php';
require_once __DIR__ . '/../models/review_model.php';
require_once __DIR__ . '/../models/promo_model.php';
require_once __DIR__ . '/../models/feedback_model.php';

$action = $_GET['action'] ?? 'dashboard';


/*

HOME / LANDING  (guest-friendly — doubles as the dashboard)

*/

if ($action === 'dashboard') {

    $popularRoutes = get_popular_routes(4);
    $topRatedBuses = array_slice(get_all_bus_ratings(), 0, 4);
    $origins = get_distinct_origins();
    $destinations = get_distinct_destinations();

    $recentTickets = [];
    $summary = null;

    if (is_logged_in()) {
        $recentTickets = get_recent_bookings(current_user_id(), 5);
        $summary = get_booking_summary(current_user_id());
    }

    $pageTitle = 'Home';
    $pageHeading = 'Welcome to ' . APP_NAME;
    $pageSub = 'Search buses and manage your tickets and journey';

    require __DIR__ . '/../views/passenger/dashboard.php';
    exit;
}


/*

SEARCH RESULTS  (guest-friendly, results loaded via AJAX)

*/

if ($action === 'search') {

    $origins = get_distinct_origins();
    $destinations = get_distinct_destinations();

    $from = clean_input($_GET['from'] ?? '');
    $to = clean_input($_GET['to'] ?? '');
    $date = clean_input($_GET['date'] ?? '');

    $pageTitle = 'Search Buses';
    $pageHeading = 'Find Your Bus';
    $pageSub = 'Search available buses and book your preferred journey';

    require __DIR__ . '/../views/passenger/search.php';
    exit;
}


/*

TRIP DETAILS / BOOKING FORM  (guest can view; login needed to proceed)

*/

if ($action === 'trip') {

    $tripId = (int) ($_GET['id'] ?? 0);
    $trip = get_trip_details($tripId);

    if (!$trip) {
        set_flash('error', 'That trip could not be found.');
        redirect('index.php?page=passenger&action=search');
    }

    $stops = get_stops_by_route($trip['route_id']);

    $pageTitle = 'Trip Details';
    $pageHeading = esc($trip['origin']) . ' &rarr; ' . esc($trip['destination']);
    $pageSub = 'Review the trip and complete your booking';

    require __DIR__ . '/../views/passenger/trip.php';
    exit;
}


/*

CONFIRM & PAY  (POST from Trip Details -> re-validate -> summary)

*/

if ($action === 'confirm') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=search');
    }

    csrf_check();

    $tripId = (int) ($_POST['trip_id'] ?? 0);
    $seats = (int) ($_POST['seats'] ?? 1);
    $boardingStopId = (int) ($_POST['boarding_stop_id'] ?? 0);
    $droppingStopId = (int) ($_POST['dropping_stop_id'] ?? 0);
    $wheelchair = isset($_POST['wheelchair']) ? 1 : 0;
    $promoCode = clean_input($_POST['promo_code'] ?? '');

    $trip = get_trip_details($tripId);

    if (!$trip || $trip['status'] !== 'scheduled') {
        set_flash('error', 'This trip is no longer available.');
        redirect('index.php?page=passenger&action=search');
    }

    if ($seats < 1 || $seats > SEATS_PER_BOOKING) {
        set_flash('error', 'You can book between 1 and ' . SEATS_PER_BOOKING . ' seats at a time.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    if ($seats > (int) $trip['available_seats']) {
        set_flash('error', 'Only ' . $trip['available_seats'] . ' seat(s) left on this trip.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    $validStopIds = array_column(get_stops_by_route($trip['route_id']), 'stop_id');

    if (
        !in_array($boardingStopId, $validStopIds, true) ||
        !in_array($droppingStopId, $validStopIds, true) ||
        $boardingStopId === $droppingStopId
    ) {
        set_flash('error', 'Please choose valid boarding and dropping points.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    $promo = null;

    if (!is_blank($promoCode)) {
        $promo = get_promo_by_code($promoCode);

        if (!$promo) {
            set_flash('error', 'That promo code is invalid or has expired.');
            redirect('index.php?page=passenger&action=trip&id=' . $tripId);
        }
    }

    $totals = calculate_total($trip['fare'], $seats, $promo);
    $boardingStop = get_stop_by_id($boardingStopId);
    $droppingStop = get_stop_by_id($droppingStopId);

    $pageTitle = 'Confirm & Pay';
    $pageHeading = 'Confirm Your Booking';
    $pageSub = 'Review the summary and choose a payment method';

    require __DIR__ . '/../views/passenger/confirm.php';
    exit;
}


/*

BOOK  (POST from Confirm & Pay -> create the booking)
Booking CRUD — Create · available_seats decremented atomically

*/

if ($action === 'book') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=search');
    }

    csrf_check();

    $tripId = (int) ($_POST['trip_id'] ?? 0);
    $seats = (int) ($_POST['seats'] ?? 1);
    $boardingStopId = (int) ($_POST['boarding_stop_id'] ?? 0);
    $droppingStopId = (int) ($_POST['dropping_stop_id'] ?? 0);
    $wheelchair = isset($_POST['wheelchair']) ? 1 : 0;
    $promoCode = clean_input($_POST['promo_code'] ?? '');
    $paymentMethod = clean_input($_POST['payment_method'] ?? '');

    $trip = get_trip_details($tripId);

    if (!$trip || $trip['status'] !== 'scheduled') {
        set_flash('error', 'This trip is no longer available.');
        redirect('index.php?page=passenger&action=search');
    }

    if ($seats < 1 || $seats > SEATS_PER_BOOKING || $seats > (int) $trip['available_seats']) {
        set_flash('error', 'Seat selection is no longer valid — please try again.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    if (!in_array($paymentMethod, ['COD', 'counter'], true)) {
        set_flash('error', 'Please choose a valid payment method.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    $promo = null;

    if (!is_blank($promoCode)) {
        $promo = get_promo_by_code($promoCode);

        if (!$promo) {
            set_flash('error', 'That promo code is no longer valid.');
            redirect('index.php?page=passenger&action=trip&id=' . $tripId);
        }
    }

    $totals = calculate_total($trip['fare'], $seats, $promo);

    // Reserve the seats first (atomic — fails if someone else just took them)
    if (!decrement_trip_seats($tripId, $seats)) {
        set_flash('error', 'Booking failed — those seats may have just been taken. Please try again.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    $bookingId = create_booking(
        current_user_id(),
        $tripId,
        $seats,
        $boardingStopId,
        $droppingStopId,
        $wheelchair,
        $promo['promo_id'] ?? null,
        $totals['total'],
        $paymentMethod
    );

    if (!$bookingId) {
        increment_trip_seats($tripId, $seats); // roll back the reservation
        set_flash('error', 'Booking failed. Please try again.');
        redirect('index.php?page=passenger&action=trip&id=' . $tripId);
    }

    set_flash('success', 'Booking confirmed! Here is your ticket.');
    redirect('index.php?page=passenger&action=viewticket&id=' . $bookingId);
}


/*

MY TICKETS  (Booking CRUD — Read, list + search)

*/

if ($action === 'mytickets') {

    require_login();

    $search = clean_input($_GET['q'] ?? '');
    $status = clean_input($_GET['status'] ?? '');

    $tickets = get_user_bookings(current_user_id(), $search, $status);

    $pageTitle = 'My Tickets';
    $pageHeading = 'My Bookings';
    $pageSub = 'View, modify, or cancel your booked bus tickets';

    require __DIR__ . '/../views/passenger/my_tickets.php';
    exit;

}
if ($action == "reviews") {

    $booking_id = $_GET['id'] ?? 0;

    $user_id = $_SESSION['user']['user_id'];


    $booking = get_booking_by_id(
        $booking_id,
        $user_id
    );


    if (!$booking) {
        echo "Booking not found";
        exit;
    }



    if ($_SERVER['REQUEST_METHOD'] == "POST") {


        $rating = $_POST['rating'];

        $comment = $_POST['comment'];


        add_review(
            $user_id,
            $booking_id,
            $booking['bus_id'],
            $rating,
            $comment
        );


        header(
            "Location:index.php?page=passenger&action=mytickets"
        );

        exit;

    }



    require __DIR__ . '/../views/passenger/reviews.php';

    exit;

}


/*

VIEW TICKET  (Booking CRUD — Read, single)

*/

if ($action === 'viewticket') {

    require_login();

    $bookingId = (int) ($_GET['id'] ?? 0);
    $booking = get_booking_by_id($bookingId, current_user_id());

    if (!$booking) {
        set_flash('error', 'Ticket not found.');
        redirect('index.php?page=passenger&action=mytickets');
    }

    $pageTitle = 'Ticket';
    $pageHeading = 'Your Ticket';
    $pageSub = 'Booking ' . ticket_code($booking['booking_id']);

    require __DIR__ . '/../views/passenger/ticket.php';
    exit;
}


/*

MODIFY BOOKING  (Booking CRUD — Update)

*/

if ($action === 'modify') {

    require_login();

    $bookingId = (int) ($_GET['id'] ?? 0);
    $booking = get_booking_by_id($bookingId, current_user_id());

    if (!$booking) {
        set_flash('error', 'Booking not found.');
        redirect('index.php?page=passenger&action=mytickets');
    }

    if ($booking['booking_status'] !== 'confirmed') {
        set_flash('error', 'Cancelled bookings cannot be modified.');
        redirect('index.php?page=passenger&action=mytickets');
    }

    if ($booking['trip_status'] !== 'scheduled') {
        set_flash('error', 'This trip has already departed and can no longer be modified.');
        redirect('index.php?page=passenger&action=viewticket&id=' . $bookingId);
    }

    $tripRow = get_trip_basic($booking['trip_id']);
    $stops = get_stops_by_route($tripRow['route_id']);

    if (is_post()) {

        csrf_check();

        $seats = (int) ($_POST['seats'] ?? $booking['seats_booked']);
        $boardingStopId = (int) ($_POST['boarding_stop_id'] ?? $booking['boarding_stop_id']);
        $droppingStopId = (int) ($_POST['dropping_stop_id'] ?? $booking['dropping_stop_id']);
        $wheelchair = isset($_POST['wheelchair']) ? 1 : 0;

        if ($seats < 1 || $seats > SEATS_PER_BOOKING) {
            set_flash('error', 'You can book between 1 and ' . SEATS_PER_BOOKING . ' seats.');
            redirect('index.php?page=passenger&action=modify&id=' . $bookingId);
        }

        $validStopIds = array_column($stops, 'stop_id');

        if (
            !in_array($boardingStopId, $validStopIds, true) ||
            !in_array($droppingStopId, $validStopIds, true) ||
            $boardingStopId === $droppingStopId
        ) {
            set_flash('error', 'Please choose valid boarding and dropping points.');
            redirect('index.php?page=passenger&action=modify&id=' . $bookingId);
        }

        $ok = modify_booking($bookingId, current_user_id(), $boardingStopId, $droppingStopId, $seats, $wheelchair);

        if ($ok) {
            set_flash('success', 'Booking updated.');
        } else {
            set_flash('error', 'Could not update booking — there may not be enough seats left.');
        }

        redirect('index.php?page=passenger&action=viewticket&id=' . $bookingId);
    }

    $pageTitle = 'Modify Booking';
    $pageHeading = 'Modify Your Booking';
    $pageSub = 'Update your seats or boarding details';

    require __DIR__ . '/../views/passenger/modify.php';
    exit;
}


/*

CANCEL BOOKING  (Booking CRUD — Delete/cancel; POST + CSRF only)

*/

if ($action === 'cancel') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=mytickets');
    }

    csrf_check();

    $bookingId = (int) ($_POST['id'] ?? 0);

    if (cancel_booking($bookingId, current_user_id())) {
        set_flash('success', 'Booking cancelled.');
    } else {
        set_flash('error', 'That booking could not be cancelled.');
    }

    redirect('index.php?page=passenger&action=mytickets');
}


/*

UNIQUE FEATURE: PAYMENT SYSTEM (COD / Counter)

*/

if ($action === 'pay') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=mytickets');
    }

    csrf_check();

    $bookingId = (int) ($_POST['id'] ?? 0);
    $method = clean_input($_POST['payment_method'] ?? '');
    $markPaid = isset($_POST['mark_paid']);

    if (!in_array($method, ['COD', 'counter'], true)) {
        $method = null;
    }

    if (update_payment($bookingId, current_user_id(), $method, $markPaid)) {
        set_flash('success', 'Payment details updated.');
    } else {
        set_flash('error', 'Could not update payment details.');
    }

    redirect('index.php?page=passenger&action=viewticket&id=' . $bookingId);
}


/*

PROFILE  (Profile CRUD)

*/

if ($action === 'profile') {

    require_login();

    $user = find_user_by_id(current_user_id());

    $pageTitle = 'Profile';
    $pageHeading = 'My Profile';
    $pageSub = 'Update your personal information';

    require __DIR__ . '/../views/passenger/profile.php';
    exit;
}


if ($action === 'updateprofile') {

    require_login();

    if (is_post()) {

        csrf_check();

        $name = clean_input($_POST['name'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');

        if (is_blank($name) || is_blank($phone)) {
            set_flash('error', 'Name and phone are required.');
            redirect('index.php?page=passenger&action=profile');
        }

        if (!valid_phone($phone)) {
            set_flash('error', 'Phone number must be exactly 11 digits.');
            redirect('index.php?page=passenger&action=profile');
        }

        if (update_profile(current_user_id(), $name, $phone)) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            set_flash('success', 'Profile updated successfully.');
        } else {
            set_flash('error', 'Profile update failed.');
        }
    }

    redirect('index.php?page=passenger&action=profile');
}


if ($action === 'changepassword') {

    require_login();

    if (is_post()) {

        csrf_check();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!verify_password(current_user_id(), $current)) {
            set_flash('error', 'Current password is incorrect.');
            redirect('index.php?page=passenger&action=profile');
        }

        if (!valid_password($new)) {
            set_flash('error', 'New password must be at least 6 characters.');
            redirect('index.php?page=passenger&action=profile');
        }

        if ($new !== $confirm) {
            set_flash('error', 'New passwords do not match.');
            redirect('index.php?page=passenger&action=profile');
        }

        update_password(current_user_id(), $new);
        set_flash('success', 'Password changed successfully.');
    }

    redirect('index.php?page=passenger&action=profile');
}


if ($action === 'deleteaccount') {

    require_login();

    if (is_post()) {

        csrf_check();

        $password = $_POST['password'] ?? '';

        if (!verify_password(current_user_id(), $password)) {
            set_flash('error', 'Incorrect password — account not deleted.');
            redirect('index.php?page=passenger&action=profile');
        }

        delete_account(current_user_id());

        $_SESSION = [];
        session_destroy();
        session_start();
        clear_remember_cookie();

        set_flash('success', 'Your account has been deleted.');
        redirect('index.php?page=login');
    }

    redirect('index.php?page=passenger&action=profile');
}


/*

REVIEWS  (Review CRUD)

*/

if ($action === 'myreviews') {

    require_login();

    $reviews = get_user_reviews(current_user_id());
    $eligibleBookings = get_completed_bookings_for_review(current_user_id());

    $editingReviewId = (int) ($_GET['edit'] ?? 0);
    $editingReview = $editingReviewId ? get_review_by_id($editingReviewId, current_user_id()) : null;

    $pageTitle = 'My Reviews';
    $pageHeading = 'My Reviews';
    $pageSub = 'Share feedback on your completed trips';

    require __DIR__ . '/../views/passenger/reviews.php';
    exit;
}


if ($action === 'addreview') {

    require_login();

    if (is_post()) {

        csrf_check();

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $busId = (int) ($_POST['bus_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = clean_input($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5 || is_blank($comment)) {
            set_flash('error', 'Please give a rating (1-5) and a comment.');
            redirect('index.php?page=passenger&action=myreviews');
        }

        // Re-confirm this booking really belongs to the user and is genuinely eligible
        $eligible = get_completed_bookings_for_review(current_user_id());
        $isEligible = false;

        foreach ($eligible as $b) {
            if ((int) $b['booking_id'] === $bookingId && (int) $b['bus_id'] === $busId) {
                $isEligible = true;
                break;
            }
        }

        if (!$isEligible) {
            set_flash('error', 'That trip is not eligible for a review.');
            redirect('index.php?page=passenger&action=myreviews');
        }

        add_review(current_user_id(), $bookingId, $busId, $rating, $comment);
        set_flash('success', 'Thanks for your review!');
    }

    redirect('index.php?page=passenger&action=myreviews');
}


if ($action === 'editreview') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=myreviews');
    }

    csrf_check();

    $reviewId = (int) ($_POST['id'] ?? 0);
    $review = get_review_by_id($reviewId, current_user_id());

    if (!$review) {
        set_flash('error', 'Review not found.');
        redirect('index.php?page=passenger&action=myreviews');
    }

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = clean_input($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5 || is_blank($comment)) {
        set_flash('error', 'Please give a rating (1-5) and a comment.');
        redirect('index.php?page=passenger&action=myreviews&edit=' . $reviewId);
    }

    update_review($reviewId, current_user_id(), $rating, $comment);
    set_flash('success', 'Review updated.');
    redirect('index.php?page=passenger&action=myreviews');
}


if ($action === 'deletereview') {

    require_login();

    if (!is_post()) {
        redirect('index.php?page=passenger&action=myreviews');
    }

    csrf_check();

    $reviewId = (int) ($_POST['id'] ?? 0);
    delete_review($reviewId, current_user_id());

    set_flash('success', 'Review deleted.');
    redirect('index.php?page=passenger&action=myreviews');
}


/*

UNIQUE FEATURE: BUS COMPANY RATING

*/

if ($action === 'busratings') {

    $ratings = get_all_bus_ratings();

    $pageTitle = 'Bus Ratings';
    $pageHeading = 'Bus Company Ratings';
    $pageSub = 'See how each bus is rated by passengers';

    require __DIR__ . '/../views/passenger/busratings.php';
    exit;
}

/*

FEEDBACK / CONTACT

*/

if ($action === 'feedback') {

    $pageTitle = 'Feedback';
    $pageHeading = 'Feedback & Contact';
    $pageSub = 'Tell us about your experience';

    require __DIR__ . '/../views/passenger/feedback.php';
    exit;
}


if ($action === 'sendfeedback') {

    if (is_post()) {

        csrf_check();

        $message = clean_input($_POST['message'] ?? '');

        if (is_blank($message)) {
            set_flash('error', 'Please write a message before sending.');
            redirect('index.php?page=passenger&action=feedback');
        }

        $userId = is_logged_in() ? current_user_id() : null;
        add_feedback($userId, $message);

        set_flash('success', 'Thanks! Your feedback has been sent.');
    }

    redirect('index.php?page=passenger&action=feedback');
}


/*
l
Fallback
ll
*/

redirect('index.php?page=passenger&action=dashboard');
