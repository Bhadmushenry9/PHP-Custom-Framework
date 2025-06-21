<?php
use App\Helpers\ViewHelper;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBTECH NIGERIA<?= ViewHelper::has('title') ? ' - ' . ViewHelper::section('title') : '' ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php ViewHelper::styles(); ?>
</head>

<body>
    <?php ViewHelper::yield('content'); ?>

    <?php ViewHelper::scripts(); ?>
</body>
</html>
