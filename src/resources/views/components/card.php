<div class="bg-white shadow-md rounded p-6 border">
    <h2 class="text-lg font-semibold mb-2"><?= htmlspecialchars($title) ?></h2>
    <div class="text-sm text-gray-700">
        <?= $content /* allow HTML in content */ ?>
    </div>
</div>