<h1>Home</h1>
<div>
<?php
use App\Helpers\ViewHelper;
ViewHelper::startSection('content');
if (!empty($invoice)): ?>
        invoiceId: <?= htmlspecialchars($invoice->id ?? ''); ?><br>
        amount: <?= htmlspecialchars($invoice->amount ?? ''); ?><br>
        user: <?= htmlspecialchars($invoice->user['full_name'] ?? ''); ?>
    <?php endif ?>
</div>
<?php
ViewHelper::endSection();
echo ViewHelper::renderLayout();
