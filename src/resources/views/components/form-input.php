<div class="mb-4">
    <label class="block text-gray-700 text-sm font-bold mb-2" for="<?= htmlspecialchars($name) ?>">
        <?= htmlspecialchars($label) ?>
    </label>
    <input 
        type="<?= htmlspecialchars($type) ?>" 
        name="<?= htmlspecialchars($name) ?>" 
        id="<?= htmlspecialchars($name) ?>" 
        value="<?= htmlspecialchars($value) ?>"
        placeholder="<?= htmlspecialchars($placeholder) ?>"
        required = <?= $required ?>
        class="shadow appearance-none border <?= $error ? 'border-red-500' : 'border-gray-300' ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
    <?php if ($error): ?>
        <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
