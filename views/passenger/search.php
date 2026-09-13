<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">Search Buses</h3>

    <form class="search-form" id="busSearchForm">

        <div class="form-group">
            <label for="from">From</label>
            <select name="from" id="from" required>
                <option value="">Select starting point</option>
                <?php foreach ($origins as $origin): ?>
                    <option value="<?= esc($origin) ?>" <?= $from === $origin ? 'selected' : '' ?>><?= esc($origin) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="to">To</label>
            <select name="to" id="to" required>
                <option value="">Select destination</option>
                <?php foreach ($destinations as $destination): ?>
                    <option value="<?= esc($destination) ?>" <?= $to === $destination ? 'selected' : '' ?>>
                        <?= esc($destination) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="journeyDate">Journey Date</label>
            <input type="date" name="date" id="journeyDate" min="<?= date('Y-m-d') ?>" value="<?= esc($date) ?>"
                required>
        </div>

        <button type="submit" class="btn btn-primary">Search Bus</button>

    </form>

</section>


<section class="search-layout">

    <aside class="filter-sidebar">

        <h4>Filters</h4>

        <div class="filter-group">
            <span class="filter-label">Bus Type</span>

            <label class="checkbox-inline">
                <input type="radio" name="busType" value="" checked> All
            </label>
            <label class="checkbox-inline">
                <input type="radio" name="busType" value="AC"> AC
            </label>
            <label class="checkbox-inline">
                <input type="radio" name="busType" value="Non-AC"> Non-AC
            </label>
        </div>

        <div class="filter-group">
            <label class="filter-label" for="sortSelect">Sort By</label>
            <select id="sortSelect">
                <option value="departure">Departure Time</option>
                <option value="fare_low">Price: Low to High</option>
                <option value="fare_high">Price: High to Low</option>
            </select>
        </div>

    </aside>

    <div class="search-results-panel">

        <h3 class="card-title">Available Buses</h3>

        <div class="bus-list" id="busResults">
            <p class="muted-text">Choose From, To and a date, then search for available buses.</p>
        </div>

    </div>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>