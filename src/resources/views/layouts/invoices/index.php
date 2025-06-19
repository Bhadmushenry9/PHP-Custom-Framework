<?php
$title = htmlspecialchars($title);

$styles = '<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; }
</style>';
?>
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
        <?php foreach ($invoices as $index => $invoice): ?>
            <?php
                $status = \App\Enums\InvoiceStatus::tryFrom($invoice['status']);
                $badge = (new \App\Views\Components\Badge(
                    $status->toString(),
                    $status->color()->value
                ))->render();
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($invoice['user']['full_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($invoice['user']['email']) ?></td>
                <td><?= number_format($invoice['amount'], 2) ?></td>
                <td><?= $badge ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
