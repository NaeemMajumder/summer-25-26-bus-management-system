# Gontobbo — Bus Management & Online Ticket Booking System (PHP + MySQL, MVC)

A university project for a 4-role bus ticketing system: **passenger, bus driver, admin, maintenance manager**.
Written in plain PHP with procedural `mysqli` and prepared statements. No frameworks,
no Composer, no build step. Copy it into XAMPP and it runs.

---

## 1. Install (XAMPP)

1. Copy the `Gontobbo` folder into `C:\xampp\htdocs\`
   so it becomes `htdocs/Gontobbo/`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` → create a database named `bus_management_system`
   → **Import** → choose `bus_management_system.sql` → **Go**.
4. Open `http://localhost/Gontobbo/`.
5. Sign in with one of the seeded test accounts below, or register a new
   Passenger / Driver / Manager account from the register page.

If your MySQL uses a password, update `$pass` in `config/config.php`.

---

## 2. Folder structure

```
Gontobbo/
├── index.php                  Front controller: the ONLY entry point (router)
├── bus_management_system.sql  Schema + seed data (test accounts, sample buses/routes/trips)
├── README.md
│
├── config/
│   └── config.php             DB connection, session settings, app constants, timezone
│
├── helpers/
│   └── helpers.php            esc(), CSRF, login guards, flash, fare/session helpers
│
├── models/                    M — every SQL query lives here
│   ├── user_model.php         all 4 roles (one users table) + admin user management
│   ├── bus_model.php          buses CRUD
│   ├── route_model.php        routes + route_stops
│   ├── trip_model.php         trip scheduling + search
│   ├── booking_model.php      booking CRUD + revenue + ticket verification
│   ├── review_model.php       reviews + Bus Company Rating
│   ├── promo_model.php        promo codes
│   ├── feedback_model.php     passenger feedback
│   ├── availability_model.php driver availability
│   ├── triplog_model.php      driver trip logs (start/complete)
│   ├── incident_model.php     driver incident reports + admin damage reports
│   ├── maintenance_model.php  manager maintenance requests
│   ├── service_model.php      manager service history + parts consumption
│   ├── part_model.php         spare parts stock
│   └── report_model.php       admin revenue/report aggregates
│
├── controllers/               C — request handling, validation, decisions
│   ├── auth_controller.php    login / register / logout / forgot-password
│   ├── passenger_controller.php
│   ├── driver_controller.php
│   ├── admin_controller.php
│   ├── manager_controller.php
│   └── ajax_controller.php    all JSON endpoints
│
├── views/                     V — HTML only
│   ├── partials/              header.php, footer.php, navbar.php (role-aware menu)
│   ├── auth/                  login.php, register.php, forgot.php
│   ├── passenger/             dashboard, search, trip, confirm, ticket, my_tickets,
│   │                          modify, profile, reviews, busratings, feedback
│   ├── driver/                dashboard, availability, incidents, logs, verify
│   ├── admin/                 dashboard, buses, routes, trips, promos, revenue,
│   │                          feedback, damage
│   └── manager/                dashboard, parts, requests, services
│
└── assets/
    ├── css/style.css
    └── js/app.js               validation, escaping, live search, AJAX (fetch)
```

**The MVC rule used throughout:** a view never runs a query, and a model never
prints HTML. The controller sits in the middle: it reads `$_POST`/`$_GET`, validates,
calls the model, then `require`s the view.

---

## 3. How the router works

Every URL looks like this:

```
index.php?page=<role>&action=<what to do>&id=<row id>
```

| URL                                                       | What happens                                        |
| --------------------------------------------------------- | --------------------------------------------------- |
| `index.php?page=login`                                    | Login page                                          |
| `index.php?page=register`                                 | Signup page (role select: Passenger/Driver/Manager) |
| `index.php?page=passenger`                                | Passenger home (doubles as the public landing page) |
| `index.php?page=driver&action=availability&edit=4`        | Load availability entry 4 into the form             |
| `index.php?page=manager&action=deletepart&id=7` (POST)    | Delete spare part 7                                 |
| `index.php?page=admin&action=updateuserrole` (POST)       | Change a user's role                                |
| `index.php?page=ajax&action=search_trips&from=&to=&date=` | JSON trip search results                            |
| `index.php?page=ajax&action=verify&code=GNT-26`           | JSON ticket check (driver-only)                     |
| `index.php?page=logout`                                   | Sign out                                            |

`index.php` loads config → helpers → models → controllers, checks the session
timeout, then sends the request to one controller. `require_role('<role>')` blocks
anyone who is not that role before the controller's logic runs. AJAX actions that
need a role check re-check it manually (see Security, below) since a redirect
would break a `fetch()` call expecting JSON.

---

## 4. The four roles

Each role manages its own set of tables and does full **Create, Read, Update, Delete
and Search** on its own dashboard. The form sits at the top of the page; the
searchable table sits below it. Clicking **Edit** reloads the same page with the
row loaded into that same form.

| Role               | Manages (CRUD)                                      | Feature 1                                        | Feature 2                  | Feature 3                      |
| ------------------ | --------------------------------------------------- | ------------------------------------------------ | -------------------------- | ------------------------------ |
| **Passenger**      | Booking · Profile · Review                          | Bus Company Rating                               | Wheelchair Special Request | Payment System (COD / Counter) |
| **Bus Driver**     | Trip Log · Availability · Incident Report           | Incident Report (doubles as its own CRUD module) | Passenger Verify (AJAX)    | Route View — _not yet built_   |
| **Admin**          | Bus · Route & Schedule · Promo Code                 | Revenue Report                                   | Feedback                   | Damage Report                  |
| **Maint. Manager** | Maintenance Request · Service History · Spare Parts | Service-Due Alert (trip limit)                   | Low Spare-Part Stock Alert | Maintenance Cost Report        |

No feature appears on two dashboards.

**Note:** the Admin dashboard also has a bonus **user management** panel (paginated
user list + change-role dropdown) in place of the spec's optional ban/suspend idea,
since the `users` table has no status column to ban/suspend against.

### How the roles connect

- Admin schedules a trip (bus + route + driver + time + fare) → it becomes
  bookable and shows up on the assigned driver's dashboard.
- Passenger searches trips and books seats → `available_seats` decreases
  (atomically, so two people can't oversell the same seat).
- At boarding, the Driver verifies the passenger's ticket code → valid/invalid,
  and it can only be one of _that driver's own_ trips.
- Driver starts a trip → completes it (trip log) → the trip's status changes and
  the bus's `trips_since_service` goes up by one.
- Once a bus passes its service trip limit, it appears on the Manager's
  Service-Due alert → Manager logs a service, spare-part stock goes down.
- At registration, the user picks a role (Passenger/Driver/Manager). Admin is
  seeded — nobody can self-register as admin, and the controller re-checks that
  server-side even if the form were tampered with. An existing admin can change
  any other user's role from the dashboard (but not their own).

---

## 5. Requirement checklist

| Requirement                 | Where to look                                                                                                                          |
| --------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| **MVC**                     | `models/`, `controllers/`, `views/` — routed by `index.php`                                                                            |
| **DB (MySQLi procedural)**  | every function in `models/` uses `mysqli_prepare`                                                                                      |
| **Auth (session + cookie)** | `controllers/auth_controller.php`, `helpers/helpers.php`                                                                               |
| **PHP validation**          | the `if / elseif` chain at the top of every controller action                                                                          |
| **JS validation**           | `validateLogin()`, `validateRegister()`, `validatePasswordMatch()` in `assets/js/app.js`                                               |
| **AJAX / JSON**             | `controllers/ajax_controller.php` (`search_trips`, `trip_details`, `route_stops`, `fare_calc`, `verify`) + `fetch()` calls in `app.js` |
| **UI (HTML/CSS)**           | `views/`, `assets/css/style.css`                                                                                                       |
| **Web security**            | see section 6                                                                                                                          |
| **Feature completeness**    | 4 roles × full CRUD + search + unique features (Driver's Route View still pending — see section 4)                                     |

---

## 6. Security, and why each piece is there

| Attack            | Defence                                                                                                          | File                                                                                                                                   |
| ----------------- | ---------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| SQL injection     | Prepared statements everywhere — user text is never glued into SQL                                               | all `models/`                                                                                                                          |
| Stolen passwords  | `password_hash()` on save, `password_verify()` on login                                                          | `user_model.php`                                                                                                                       |
| XSS (server)      | `esc()` wraps every value printed into HTML                                                                      | `helpers.php`, all views                                                                                                               |
| XSS (client)      | `escapeHtml()` before any AJAX row is inserted into the DOM                                                      | `app.js`                                                                                                                               |
| CSRF              | A secret token in every POST form and every delete/cancel link                                                   | `helpers.php`, all views                                                                                                               |
| Session fixation  | `session_regenerate_id(true)` right after a successful login                                                     | `auth_controller.php`                                                                                                                  |
| Idle machines     | Automatic sign-out after 30 minutes                                                                              | `check_session_timeout()` in `helpers.php`                                                                                             |
| Wrong role        | `require_role()` before every controller's logic runs                                                            | top of every `controllers/*_controller.php`                                                                                            |
| Wrong role (AJAX) | Manual `is_logged_in()` + `current_role()` check, since a redirect would break `fetch()`                         | `ajax_controller.php` (`verify` action)                                                                                                |
| URL tampering     | A user can only load/edit/delete their own rows (`WHERE … AND user_id = ?` / `driver_id = ?` / `manager_id = ?`) | `booking_model.php`, `triplog_model.php`, `availability_model.php`, `incident_model.php`, `maintenance_model.php`, `service_model.php` |
| Username guessing | Wrong email and wrong password give the same message                                                             | `auth_controller.php`                                                                                                                  |
| Self-lockout      | An admin cannot change their own role                                                                            | `admin_controller.php`                                                                                                                 |

Two things worth saying out loud:

1. **JavaScript validation is a convenience, not a defence.** Anyone can turn
   JavaScript off. That is why every controller repeats the checks in PHP.
2. **"Remember me" only refills the email field on the login form** — it stores
   the email in a plain cookie, never the password, and it is not a login
   session by itself.

**Honest gaps, not hidden:** the "Forgot Password" flow generates a real reset
token but skips actually sending an email (no mail server is configured) —
it shows the reset screen directly with a flash message saying so. This is a
known simplification for a local/demo environment, not a bug.

---

## 7. Settings you can change

All in `config/config.php`:

```php
define('APP_NAME', 'Gontobbo');
define('SEATS_PER_BOOKING', 4);    // max seats per booking
define('SESSION_TIMEOUT', 1800);   // idle sign-out, in seconds
define('CURRENCY', '৳');            // symbol shown next to prices
define('FINE_PER_DAY', 0);          // reserved, not used by any feature yet
define('PARTS_LOW_STOCK', 5);       // at or below this, a part shows a "Low" badge

date_default_timezone_set('Asia/Dhaka'); // matters for "today's trips" on the
                                          // Driver dashboard — without this PHP
                                          // defaults to UTC and dates can be off
                                          // by several hours around midnight.
```

**Note:** the service trip limit is _not_ a global constant here — it is stored
per-bus as `buses.service_trip_limit`, set individually when a bus is added or
edited by the Admin. This is more flexible than a single fixed number for every
bus.

---

## 8. Test accounts

| Role           | Email                 | Password    |
| -------------- | --------------------- | ----------- |
| Admin          | `admin@bus.com`       | `adminbus`  |
| Passenger      | `passenger@gmail.com` | `passenger` |
| Bus Driver     | `driver@gmail.com`    | `driver`    |
| Maint. Manager | `manager@gmail.com`   | `manager`   |

More accounts of any non-admin role can be created any time from the register
page. Nobody can sign up as an admin — the register page only accepts
Passenger/Driver/Manager, and the controller checks that list again on the
server.

---

## Copyright (c) 2026 Naeem Majumder, Abdur Rahman Alvi, MD. Shafi Mahmmod, MD. Rafsan Tahlil Rifat. All rights reserved.
