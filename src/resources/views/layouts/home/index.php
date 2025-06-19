<h1>Home</h1>
<div>
    <?php if (!empty($invoice)): ?>
        invoiceId: <?= htmlspecialchars($invoice->id ?? ''); ?><br>
        amount: <?= htmlspecialchars($invoice->amount ?? ''); ?><br>
        user: <?= htmlspecialchars($invoice->user['full_name'] ?? ''); ?>
    <?php endif ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
