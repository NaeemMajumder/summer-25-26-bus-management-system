<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card card-narrow">
    <h3 class="card-title">Verify Passenger</h3>
    <p class="card-note">Enter the ticket code shown on the passenger's ticket (e.g. GNT-000007).</p>

    <div class="form-group">
        <label for="ticket_code">Ticket Code</label>
        <input type="text" id="ticket_code" placeholder="GNT-000007" autofocus>
    </div>

    <button type="button" id="verifyBtn" class="btn btn-primary">Verify</button>

    <div id="verifyResult" style="margin-top: 20px;"></div>
</section>

<script>
    document.getElementById('verifyBtn').addEventListener('click', function () {
        var code = document.getElementById('ticket_code').value.trim();
        var resultBox = document.getElementById('verifyResult');

        if (!code) {
            resultBox.innerHTML = '<div class="alert alert-error">Please enter a ticket code.</div>';
            return;
        }

        resultBox.innerHTML = '<p class="muted-text">Checking...</p>';

        fetch('index.php?page=ajax&action=verify&code=' + encodeURIComponent(code))
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.status !== 'success') {
                    resultBox.innerHTML = '<div class="alert alert-error">' + json.message + '</div>';
                    return;
                }

                var d = json.data;
                resultBox.innerHTML =
                    '<div class="alert alert-success">Valid ticket</div>' +
                    '<div class="ticket-row"><span>Passenger</span><span>' + d.passenger_name + '</span></div>' +
                    '<div class="ticket-row"><span>Route</span><span>' + d.route + '</span></div>' +
                    '<div class="ticket-row"><span>Trip Date</span><span>' + d.trip_date + '</span></div>' +
                    '<div class="ticket-row"><span>Seats</span><span>' + d.seats_booked + '</span></div>' +
                    '<div class="ticket-row"><span>Boarding</span><span>' + d.boarding_stop + '</span></div>' +
                    '<div class="ticket-row"><span>Dropping</span><span>' + d.dropping_stop + '</span></div>' +
                    '<div class="ticket-row"><span>Wheelchair</span><span>' + (d.wheelchair ? 'Yes' : 'No') + '</span></div>' +
                    '<div class="ticket-row"><span>Payment</span><span>' + d.payment_status + '</span></div>';
            })
            .catch(function () {
                resultBox.innerHTML = '<div class="alert alert-error">Something went wrong. Try again.</div>';
            });
    });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>