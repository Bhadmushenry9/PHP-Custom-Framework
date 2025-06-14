<h1><?= $title ?></h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        foreach ($invoices as $invoice) {
            $status = App\Enums\InvoiceStatus::tryFrom(htmlspecialchars($invoice['status']));
            ?>
            <tr>
                <td><?= $count ?></td>
                <td><?= htmlspecialchars($invoice['user']['full_name'] ?? ''); ?></td>
                <td><?= htmlspecialchars($invoice['user']['email']) ?></td>
                <td><?= htmlspecialchars(number_format($invoice['amount'], 2)) ?></td>
                <td><?= (new App\Views\Components\Badge($status->toString(), $status->color()->value))->render() ?>
                </td>
            </tr>
            <?php $count++;
        } ?>
    </tbody>
</table>

<?php

$styles = '<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; }
</style>';