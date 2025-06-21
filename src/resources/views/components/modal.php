<div id="<?= htmlspecialchars($id) ?>" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4"><?= htmlspecialchars($title) ?></h3>
        <div class="mb-4"><?= $content /* allow HTML */ ?></div>
        <div class="text-right">
            <button onclick="document.getElementById('<?= htmlspecialchars($id) ?>').classList.add('hidden')" 
                    class="px-4 py-2 bg-gray-700 text-white rounded">Close</button>
        </div>
    </div>
</div>