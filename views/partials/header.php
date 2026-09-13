<?php

/*
==========================================================
views/partials/header.php
==========================================================
*/

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($pageTitle ?? APP_NAME) ?> - <?= esc(APP_NAME) ?></title>

    <link rel="stylesheet" href="assets/css/style.css?v=4">
</head>

<body class="app-body">

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1 class="page-title"><?= $pageHeading ?? '' ?></h1>
                <p class="page-sub"><?= esc($pageSub ?? '') ?></p>
            </div>

            <?php if (!empty($headerAction)): ?>
                <div><?= $headerAction ?></div>
            <?php endif; ?>
        </div>

        <?php foreach (get_flash() as $flash): ?>
            <div class="alert alert-<?= esc($flash['type']) ?>">
                <?= esc($flash['message']) ?>
            </div>
        <?php endforeach; ?>