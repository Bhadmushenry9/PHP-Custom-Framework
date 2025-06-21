<?php

use App\Helpers\ViewHelper;

$title = htmlspecialchars($title);
ViewHelper::setTitle($title);
ViewHelper::startSection('content');

?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= $title ?></h1>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($invoices as $index => $invoice): ?>
                    <?php
                    $status = \App\Enums\InvoiceStatus::tryFrom($invoice['status']);
                    $badge = (new \App\Views\Components\Badge(
                        $status->toString(),
                        $status->color()->value
                    ))->render();
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $index + 1 ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($invoice['user']['full_name'] ?? '') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($invoice['user']['email']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            <?= number_format($invoice['amount'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right"><?= $badge ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= (new \App\Views\Components\Paginator($invoices))->render() ?>
    </div>
</div>

<?php
ViewHelper::endSection();
echo ViewHelper::renderLayout();
