<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBTECH NIGERIA<?= isset($title) ? ' - ' . htmlspecialchars($title) : '' ?></title>

    <?php if (!empty($styles)): ?>
        <!-- Extra Styles -->
        <?= $styles ?>
    <?php endif; ?>
</head>

<body>
    <?= $content ?>

    <?php if (!empty($scripts)): ?>
        <!-- Extra Scripts -->
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>
