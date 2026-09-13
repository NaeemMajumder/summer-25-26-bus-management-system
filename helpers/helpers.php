<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




/*
==========================
Security Helpers
==========================
*/


function esc($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


function clean_input($value)
{
    return trim(
        htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
==========================
Redirect
==========================
*/


function redirect($url)
{
    header("Location: " . $url);
    exit;
}


function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


/*
==========================
CSRF Protection
==========================
*/


function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


function csrf_field()
{
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}


function csrf_check()
{
    $token = $_POST['csrf_token'] ?? '';

    if (empty($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die("Security check failed. Please go back and try again.");
    }
}


/*
==========================
Authentication
==========================
*/


function is_logged_in()
{
    return isset($_SESSION['user']);
}


function current_user()
{
    return $_SESSION['user'] ?? null;
}


function current_user_id()
{
    return $_SESSION['user']['user_id'] ?? 0;
}


function current_user_name()
{
    return $_SESSION['user']['name'] ?? 'Guest';
}


function current_role()
{
    return $_SESSION['user']['role'] ?? '';
}


function require_login()
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('index.php?page=login');
    }
}


function require_role($role)
{
    require_login();

    if (current_role() !== $role) {
        set_flash('error', 'You are not authorised to view that page.');
        redirect('index.php');
    }
}


/*
==========================
Session Timeout (idle auto sign-out)
==========================
*/


function check_session_timeout()
{
    if (!is_logged_in()) {
        return;
    }

    $last = $_SESSION['last_activity'] ?? time();

    if ((time() - $last) > SESSION_TIMEOUT) {

        $_SESSION = [];
        session_destroy();

        session_start();
        set_flash('error', 'You were signed out after a period of inactivity.');
        redirect('index.php?page=login');
    }

    $_SESSION['last_activity'] = time();
}


/*
==========================
Remember-Me Cookie
==========================
*/


function set_remember_cookie($token)
{
    setcookie('remember_token', $token, [
        'expires'  => time() + (86400 * 30),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}


function clear_remember_cookie()
{
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}


/*
==========================
Flash Message
==========================
*/


function set_flash($type, $message)
{
    $_SESSION['flash'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}


function get_flash()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}


/*
==========================
Old Input (repopulate a form after a validation redirect)
==========================
*/


function set_old($data)
{
    $_SESSION['old'] = $data;
}


function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}


function clear_old()
{
    unset($_SESSION['old']);
}


/*
==========================
Validation
==========================
*/


function valid_email($email)
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}


function valid_phone($phone)
{
    return (bool) preg_match('/^[0-9]{11}$/', $phone);
}


function valid_password($password)
{
    return strlen($password) >= 6;
}

function valid_idnum($value)
{
    // NID / License: digits, letters, dash, space — 5 to 30 chars
    return (bool) preg_match('/^[A-Za-z0-9\- ]{5,30}$/', trim((string) $value));
}


function is_blank($value)
{
    return trim((string) $value) === '';
}


function valid_date($date)
{
    $parts = explode('-', $date);

    return count($parts) === 3
        && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}


/*
==========================
Booking / Fare Helpers
==========================
*/


// $promo is an associative array from promo_codes (or null for no discount)
function calculate_total($fare, $seats, $promo = null)
{
    $subtotal = round($fare * $seats, 2);
    $discount = 0.0;

    if ($promo) {

        if ($promo['discount_type'] === 'percent') {
            $discount = round($subtotal * ((float) $promo['discount_value'] / 100), 2);
        } else { // flat
            $discount = round((float) $promo['discount_value'], 2);
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
    }

    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total'    => round($subtotal - $discount, 2),
    ];
}


function format_currency($amount)
{
    return CURRENCY . number_format((float) $amount, 2);
}


function ticket_code($booking_id)
{
    return 'GNT-' . (int) $booking_id;
}


function booking_status_label($status)
{
    $statusList = [
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ];

    return $statusList[$status] ?? ucfirst((string) $status);
}


function payment_status_label($status)
{
    $statusList = [
        'paid'      => 'Paid',
        'pending'   => 'Pending',
        'cancelled' => 'Cancelled',
    ];

    return $statusList[$status] ?? ucfirst((string) $status);
}


function payment_method_label($method)
{
    $methods = [
        'COD'     => 'Cash on Boarding',
        'counter' => 'Pay at Counter',
    ];

    return $methods[$method] ?? ucfirst((string) $method);
}


function star_rating_html($avg, $count = null)
{
    $avg  = (float) $avg;
    $full = (int) round($avg);
    $html = '<span class="stars" aria-label="' . esc(number_format($avg, 1)) . ' out of 5">';

    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $full ? '&#9733;' : '&#9734;';
    }

    $html .= '</span> <span class="rating-number">' . esc(number_format($avg, 1)) . '</span>';

    if ($count !== null) {
        $html .= ' <span class="rating-count">(' . esc($count) . ')</span>';
    }

    return $html;
}


/*
==========================
JSON Response
==========================
*/


function json_out($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}
