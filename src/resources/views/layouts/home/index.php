<h1>Home</h1>
<hr />
<div>
    <?php if (!empty($invoice)): ?>
        invoiceId: <?= htmlspecialchars($invoice->id ?? ''); ?><br>
        amount: <?= htmlspecialchars($invoice->amount ?? ''); ?><br>
        user: <?= htmlspecialchars($invoice->user['full_name'] ?? ''); ?>
    <?php endif ?>
</div>