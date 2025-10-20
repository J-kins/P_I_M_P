<?php
/**
 * Pagination Component with Icons
 */
function Pagination($props = []) {
    $currentPage = $props['currentPage'] ?? 1;
    $totalPages = $props['totalPages'] ?? 1;
    $baseUrl = $props['baseUrl'] ?? '';
    $queryParams = $props['queryParams'] ?? $_GET;
    
    // Remove page from query params
    unset($queryParams['page']);
    
    $queryString = http_build_query($queryParams);
    if ($queryString) {
        $queryString = '&' . $queryString;
    }
    
    ob_start();
    ?>
    <nav class="pagination" aria-label="Search results pages">
        <ul class="pagination__list">
            <!-- Previous Button -->
            <li class="pagination__item <?php echo $currentPage <= 1 ? 'pagination__item--disabled' : ''; ?>">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo htmlspecialchars($baseUrl); ?>?page=<?php echo $currentPage - 1 . $queryString; ?>" class="pagination__link pagination__link--prev">
                        <svg class="pagination-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Previous
                    </a>
                <?php else: ?>
                    <span class="pagination__link pagination__link--disabled">
                        <svg class="pagination-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Previous
                    </span>
                <?php endif; ?>
            </li>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                    <li class="pagination__item <?php echo $i == $currentPage ? 'pagination__item--active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars($baseUrl); ?>?page=<?php echo $i . $queryString; ?>" class="pagination__link">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                    <li class="pagination__item pagination__item--ellipsis">
                        <span class="pagination__link">...</span>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="pagination__item <?php echo $currentPage >= $totalPages ? 'pagination__item--disabled' : ''; ?>">
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo htmlspecialchars($baseUrl); ?>?page=<?php echo $currentPage + 1 . $queryString; ?>" class="pagination__link pagination__link--next">
                        Next
                        <svg class="pagination-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="pagination__link pagination__link--disabled">
                        Next
                        <svg class="pagination-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <?php
    return ob_get_clean();
}
?>
