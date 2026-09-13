/*
==========================================================
assets/js/app.js

Vanilla JS only — no framework. DOM manipulation + fetch()
for every AJAX call, matching controllers/ajax_controller.php.
==========================================================
*/

/*
==========================
Small Helpers
==========================
*/

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function formatTime(timeStr) {
  if (!timeStr) return "";
  const parts = timeStr.split(":");
  let hours = parseInt(parts[0], 10);
  const minutes = parts[1] || "00";
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12;
  if (hours === 0) hours = 12;
  return hours + ":" + minutes + " " + ampm;
}

function renderStars(avg) {
  const full = Math.round(Number(avg) || 0);
  let html = '<span class="stars">';
  for (let i = 1; i <= 5; i++) {
    html += i <= full ? "&#9733;" : "&#9734;";
  }
  html += "</span>";
  return html;
}

/*
==========================
Search Results (AJAX)
==========================
*/

function runSearch() {
  const fromEl = document.getElementById("from");
  const toEl = document.getElementById("to");
  const dateEl = document.getElementById("journeyDate");
  const resultBox = document.getElementById("busResults");

  if (!resultBox) {
    return;
  }

  const from = fromEl ? fromEl.value : "";
  const to = toEl ? toEl.value : "";
  const date = dateEl ? dateEl.value : "";

  if (from && to && from === to) {
    resultBox.innerHTML =
      '<p class="muted-text">Origin and destination cannot be the same.</p>';
    return;
  }

  const typeInput = document.querySelector('input[name="busType"]:checked');
  const type = typeInput ? typeInput.value : "";
  const sortSelect = document.getElementById("sortSelect");
  const sort = sortSelect ? sortSelect.value : "departure";

  resultBox.innerHTML = '<p class="muted-text">Searching for buses...</p>';

  const params = new URLSearchParams({
    page: "ajax",
    action: "search_trips",
    from: from,
    to: to,
    date: date,
    type: type,
    sort: sort,
  });

  fetch("index.php?" + params.toString())
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Server error");
      }
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        resultBox.innerHTML =
          '<p class="muted-text">' +
          escapeHtml(data.message || "Search failed.") +
          "</p>";
        return;
      }

      if (!data.data || data.data.length === 0) {
        resultBox.innerHTML =
          '<div class="empty-box"><p>No buses found for this route and date.</p></div>';
        return;
      }

      resultBox.innerHTML = "";

      data.data.forEach(function (bus) {
        const card = document.createElement("div");
        card.className = "bus-card";

        card.innerHTML =
          "<div>" +
          "<h4>" +
          escapeHtml(bus.bus_name) +
          ' <span class="badge badge-' +
          (bus.bus_type === "AC" ? "ac" : "nonac") +
          '">' +
          escapeHtml(bus.bus_type) +
          "</span>" +
          "</h4>" +
          "<p>" +
          escapeHtml(bus.origin) +
          " &rarr; " +
          escapeHtml(bus.destination) +
          "</p>" +
          "<p>Departure: " +
          escapeHtml(formatTime(bus.departure_time)) +
          " &middot; Arrival: " +
          escapeHtml(formatTime(bus.arrival_time)) +
          "</p>" +
          "<p>" +
          renderStars(bus.avg_rating) +
          ' <span class="rating-count">(' +
          bus.rating_count +
          ")</span></p>" +
          "</div>" +
          '<div class="bus-card-side">' +
          '<p class="trip-fare">' +
          escapeHtml(bus.fare_display) +
          "</p>" +
          '<p class="muted-text">' +
          bus.available_seats +
          " seat(s) left</p>" +
          '<a href="index.php?page=passenger&action=trip&id=' +
          encodeURIComponent(bus.trip_id) +
          '" class="btn btn-primary">Select</a>' +
          "</div>";

        resultBox.appendChild(card);
      });
    })
    .catch(function () {
      resultBox.innerHTML =
        '<p class="muted-text">Something went wrong while searching. Please try again.</p>';
    });
}

/*
==========================
Seat +/- Control (Trip Details + Modify Booking pages)
==========================
*/

function initSeatControl(onChangeCallback) {
  const seatInput = document.getElementById("seatCount");
  const minusBtn = document.getElementById("minusSeat");
  const plusBtn = document.getElementById("plusSeat");

  if (!seatInput || !minusBtn || !plusBtn) {
    return;
  }

  minusBtn.addEventListener("click", function () {
    const min = parseInt(seatInput.min, 10) || 1;
    let value = parseInt(seatInput.value, 10) || min;

    if (value > min) {
      seatInput.value = value - 1;
      if (onChangeCallback) onChangeCallback();
    }
  });

  plusBtn.addEventListener("click", function () {
    const max = parseInt(seatInput.max, 10) || 999;
    let value = parseInt(seatInput.value, 10) || 1;

    if (value < max) {
      seatInput.value = value + 1;
      if (onChangeCallback) onChangeCallback();
    }
  });
}

/*
==========================
Live Fare Calculation (Trip Details page)
==========================
*/

function initFareCalc() {
  if (typeof window.TRIP_ID === "undefined") {
    return null;
  }

  const seatInput = document.getElementById("seatCount");
  const promoInput = document.getElementById("promoCode");
  const subtotalEl = document.getElementById("fareSubtotal");
  const totalEl = document.getElementById("fareTotal");
  const discountRow = document.getElementById("discountRow");
  const discountEl = document.getElementById("fareDiscount");
  const promoNoteEl = document.getElementById("promoNote");

  if (!seatInput || !subtotalEl || !totalEl) {
    return null;
  }

  let debounceTimer = null;

  function recalc() {
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(function () {
      const seats = seatInput.value;
      const promoCode = promoInput ? promoInput.value.trim() : "";

      const params = new URLSearchParams({
        page: "ajax",
        action: "fare_calc",
        trip_id: window.TRIP_ID,
        seats: seats,
        promo_code: promoCode,
      });

      fetch("index.php?" + params.toString())
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.status !== "success") {
            if (promoNoteEl) {
              promoNoteEl.textContent = data.message || "";
              promoNoteEl.className = "promo-note promo-invalid";
            }
            return;
          }

          subtotalEl.textContent =
            window.CURRENCY_SYMBOL + Number(data.subtotal).toFixed(2);
          totalEl.textContent = data.total_display;

          if (discountRow && discountEl) {
            if (data.discount > 0) {
              discountRow.style.display = "";
              discountEl.textContent =
                "-" + window.CURRENCY_SYMBOL + Number(data.discount).toFixed(2);
            } else {
              discountRow.style.display = "none";
            }
          }

          if (promoNoteEl) {
            if (!promoCode) {
              promoNoteEl.textContent = "";
              promoNoteEl.className = "promo-note";
            } else {
              promoNoteEl.textContent = data.promo_note || "";
              promoNoteEl.className =
                "promo-note " +
                (data.promo_valid ? "promo-valid" : "promo-invalid");
            }
          }
        })
        .catch(function () {
          /* silently keep last known total */
        });
    }, 300);
  }

  seatInput.addEventListener("change", recalc);

  if (promoInput) {
    promoInput.addEventListener("input", recalc);
  }

  return recalc;
}

/*
==========================
Form Validation (client-side; the server always re-checks)
==========================
*/

function validateLogin() {
  clearErrors();
  let ok = true;

  const email = document.getElementById("loginEmail");
  const password = document.getElementById("loginPassword");

  if (!email || !password) return true;

  if (email.value.trim() === "") {
    showError("loginEmail", "err-login-email", "Email is required.");
    ok = false;
  }
  if (password.value === "") {
    showError("loginPassword", "err-login-password", "Password is required.");
    ok = false;
  }

  return ok;
}

/*
==========================
Inline error helpers
==========================
*/

function showError(fieldId, errId, message) {
  const field = document.getElementById(fieldId);
  const err = document.getElementById(errId);
  if (err) err.textContent = message;
  if (field) field.classList.add("input-error");
}

function clearErrors() {
  document.querySelectorAll(".field-error").forEach(function (el) {
    el.textContent = "";
  });
  document.querySelectorAll(".input-error").forEach(function (el) {
    el.classList.remove("input-error");
  });
}

function validateRegister() {
  clearErrors();

  let ok = true;

  const name = document.getElementById("regName");
  const email = document.getElementById("regEmail");
  const phone = document.getElementById("regPhone");
  const password = document.getElementById("regPassword");
  const confirm = document.getElementById("regConfirmPassword");
  const role = document.getElementById("roleInput");

  // Common fields
  if (!name.value.trim()) {
    showError("regName", "err-name", "Name is required.");
    ok = false;
  }

  if (!email.value.trim()) {
    showError("regEmail", "err-email", "Email is required.");
    ok = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    showError("regEmail", "err-email", "Please enter a valid email address.");
    ok = false;
  }

  if (!/^[0-9]{11}$/.test(phone.value.trim())) {
    showError(
      "regPhone",
      "err-phone",
      "Phone number must be exactly 11 digits.",
    );
    ok = false;
  }

  // Role-specific field
  const roleValue = role ? role.value : "passenger";

  if (roleValue === "passenger") {
    const nid = document.getElementById("regNid");
    if (nid && !nid.value.trim()) {
      showError("regNid", "err-nid_number", "NID number is required.");
      ok = false;
    }
  } else if (roleValue === "driver") {
    const license = document.getElementById("regLicense");
    if (license && !license.value.trim()) {
      showError(
        "regLicense",
        "err-license_number",
        "License number is required.",
      );
      ok = false;
    }
  } else if (roleValue === "manager") {
    const exp = document.getElementById("regExp");
    if (exp && (exp.value === "" || Number(exp.value) < 0)) {
      showError(
        "regExp",
        "err-experience_years",
        "Please enter your years of experience.",
      );
      ok = false;
    }
    // previous_company is optional — no check
  }

  // Password
  if (password.value.length < 6) {
    showError(
      "regPassword",
      "err-password",
      "Password must be at least 6 characters.",
    );
    ok = false;
  }

  if (password.value !== confirm.value) {
    showError(
      "regConfirmPassword",
      "err-confirm_password",
      "Passwords do not match.",
    );
    ok = false;
  }

  return ok;
}

/*
==========================
Role toggle (Register page)
==========================
*/

function initRoleToggle() {
  const toggle = document.getElementById("roleToggle");
  const roleInput = document.getElementById("roleInput");
  if (!toggle || !roleInput) return;

  toggle.querySelectorAll(".role-option").forEach(function (btn) {
    btn.addEventListener("click", function () {
      // highlight the clicked button
      toggle.querySelectorAll(".role-option").forEach(function (b) {
        b.classList.remove("active");
      });
      btn.classList.add("active");

      // set the hidden role value
      const role = btn.getAttribute("data-role");
      roleInput.value = role;

      // show only the matching field group
      document.querySelectorAll(".role-fields").forEach(function (group) {
        group.classList.toggle(
          "active",
          group.getAttribute("data-role-fields") === role,
        );
      });

      clearErrors();
    });
  });
}

/*
==========================
Password eye toggle
==========================
*/

function initPasswordEyes() {
  document.querySelectorAll(".toggle-eye").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const target = document.getElementById(btn.getAttribute("data-target"));
      if (!target) return;

      if (target.type === "password") {
        target.type = "text";
        btn.classList.add("showing");
      } else {
        target.type = "password";
        btn.classList.remove("showing");
      }
    });
  });
}

function validatePasswordMatch(newId, confirmId) {
  const newP = document.getElementById(newId);
  const confP = document.getElementById(confirmId);

  if (!newP || !confP) return true;

  if (newP.value.length < 6) {
    alert("Password must be at least 6 characters.");
    return false;
  }

  if (newP.value !== confP.value) {
    alert("Passwords do not match.");
    return false;
  }

  return true;
}

/*
==========================
Bootstrap
==========================
*/
const searchForm = document.getElementById("busSearchForm");
if (searchForm) {
  console.log("Search form found");

  searchForm.addEventListener("submit", function (event) {
    event.preventDefault();
    console.log("Search clicked");
    runSearch();
  });


  document
    .querySelectorAll('input[name="busType"]')
    .forEach(function (radio) {
      radio.addEventListener("change", runSearch);
    });

  const sortSelect = document.getElementById("sortSelect");
  if (sortSelect) {
    sortSelect.addEventListener("change", runSearch);
  }

  runSearch();
}

/* Trip Details / Booking page — seat control wired to live fare recalculation */
const recalc = initFareCalc();
initSeatControl(recalc);

/* Login / Register client-side validation */
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", function (event) {
    if (!validateLogin()) event.preventDefault();
  });
}

const registerForm = document.getElementById("registerForm");
if (registerForm) {
  registerForm.addEventListener("submit", function (event) {
    if (!validateRegister()) event.preventDefault();
  });
}

initRoleToggle();
initPasswordEyes();

/* Any form with a New Password + Confirm Password pair (change password / reset password) */
document.querySelectorAll("form").forEach(function (form) {
  if (
    form.querySelector("#newPassword") &&
    form.querySelector("#confirmNewPassword")
  ) {
    form.addEventListener("submit", function (event) {
      if (!validatePasswordMatch("newPassword", "confirmNewPassword")) {
        event.preventDefault();
      }
    });
  }
});