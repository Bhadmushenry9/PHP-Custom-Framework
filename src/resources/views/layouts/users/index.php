<?php
use App\Helpers\ViewHelper;
ViewHelper::setTitle('Users');
ViewHelper::startSection('content');
?>

<div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">All Users</h1>

    <?php if (empty($users)): ?>
        <div class="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded">
            No users found.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($users as $index => $user): ?>
                        <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                            <td class="px-4 py-2 text-sm text-gray-700"><?= $index + 1 ?></td>
                            <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?= (new \App\Views\Components\Paginator($users))->render() ?>
        </div>
    <?php endif; ?>
</div>

<?php
ViewHelper::endSection();
echo ViewHelper::renderLayout();
