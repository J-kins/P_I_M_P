<?php
/**
 * BBB-style Breadcrumbs Component
 * 
 * @param array $items Array of breadcrumb items [['url' => '', 'label' => ''], ...]
 * @param string $homeText Home link text (default: "Home")
 * @param string $homeUrl Home URL (default: "/")
 * @return string HTML output
 */
function bbb_breadcrumbs(array $items = [], string $homeText = 'Home', string $homeUrl = '/'): string {
    ob_start(); ?>
    <nav class="bbb-breadcrumbs" aria-label="Breadcrumb">
        <ol class="bbb-breadcrumbs-list">
            <li class="bbb-breadcrumbs-item">
                <a href="<?= url($homeUrl) ?>" class="bbb-breadcrumbs-link">
                    <?= htmlspecialchars($homeText) ?>
                </a>
                <span class="bbb-breadcrumbs-separator">›</span>
            </li>
            <?php foreach ($items as $index => $item): ?>
                <li class="bbb-breadcrumbs-item">
                    <?php if ($index === count($items) - 1): ?>
                        <span class="bbb-breadcrumbs-current" aria-current="page">
                            <?= htmlspecialchars($item['label']) ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= url($item['url']) ?>" class="bbb-breadcrumbs-link">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                        <span class="bbb-breadcrumbs-separator">›</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php return ob_get_clean();
}
?>
