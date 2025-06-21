<?php if ($paginator->lastPage() > 1): ?>
    <div class="mt-4 flex justify-center">
        <nav class="inline-flex space-x-2" aria-label="Pagination">
            <?php if ($paginator->onFirstPage()): ?>
                <span class="px-3 py-2 text-sm text-gray-400 border border-gray-300 rounded">Previous</span>
            <?php else: ?>
                <a href="<?= $paginator->previousPageUrl() ?>"
                   class="px-3 py-2 text-sm text-blue-600 border border-gray-300 rounded hover:bg-gray-100">
                    Previous
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $paginator->lastPage(); $i++): ?>
                <?php if ($i === $paginator->currentPage()): ?>
                    <span class="px-3 py-2 text-sm text-white bg-blue-600 border border-blue-600 rounded"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $paginator->url($i) ?>"
                       class="px-3 py-2 text-sm text-blue-600 border border-gray-300 rounded hover:bg-gray-100"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paginator->hasMorePages()): ?>
                <a href="<?= $paginator->nextPageUrl() ?>"
                   class="px-3 py-2 text-sm text-blue-600 border border-gray-300 rounded hover:bg-gray-100">
                    Next
                </a>
            <?php else: ?>
                <span class="px-3 py-2 text-sm text-gray-400 border border-gray-300 rounded">Next</span>
            <?php endif; ?>
        </nav>
    </div>
<?php endif; ?>
