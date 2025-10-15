
<?php
/**
 * UI Elements Component Library
 * 
 * This file contains reusable UI components for the PHP UI Template System
 */

/**
 * Toast notification component
 * 
 * @param string $type     Type of toast (neutral, info, success, warning, error)
 * @param string $title    Toast title text
 * @param string $message  Toast message text
 * @param bool   $closable Whether toast can be closed (default: true)
 * @param int    $timeout  Auto-close timeout in ms (0 for no auto-close)
 * @return string HTML output
 */
/*
function toast($type = 'neutral', $title = '', $message = '', $closable = true, $timeout = 0) {
    // Define toast types with their icons and colors
    $types = [
        'neutral' => [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
            'class' => 'toast-neutral'
        ],
        'info' => [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
            'class' => 'toast-info'
        ],
        'success' => [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            'class' => 'toast-success'
        ],
        'warning' => [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            'class' => 'toast-warning'
        ],
        'error' => [
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            'class' => 'toast-error'
        ]
    ];
    
    // Default to neutral if type doesn't exist
    if (!isset($types[$type])) {
        $type = 'neutral';
    }
    
    $toastId = 'toast-' . uniqid();
    $typeData = $types[$type];
    
    ob_start(); ?>
    <div id="<?= $toastId ?>" class="toast <?= $typeData['class'] ?>" role="alert"
        <?= $timeout > 0 ? 'data-timeout="' . $timeout . '"' : '' ?>>
        <div class="toast-icon">
            <?= $typeData['icon'] ?>
        </div>
        <div class="toast-content">
            <?php if (!empty($title)): ?>
                <div class="toast-title"><?= htmlspecialchars($title) ?></div>
            <?php endif; ?>
            <?php if (!empty($message)): ?>
                <div class="toast-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </div>
        <?php if ($closable): ?>
            <button type="button" class="toast-close" aria-label="Close" onclick="this.parentElement.remove()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        <?php endif; ?>
    </div>
    <?php if ($timeout > 0): ?>
    <script>
        setTimeout(function() {
            const toast = document.getElementById('<?= $toastId ?>');
            if (toast) toast.remove();
        }, <?= $timeout ?>);
    </script>
    <?php endif;
    return ob_get_clean();
}
*/

/**
 * Toast group for showing multiple toasts in a container
 * 
 * @param array $toasts Array of toast parameters
 * @param string $position Position (top-right, top-left, bottom-right, bottom-left)
 * @return string HTML output
 */
function toastGroup($toasts = [], $position = 'top-right') {
    ob_start(); ?>
    <div class="toast-container toast-<?= htmlspecialchars($position) ?>">
        <?php foreach ($toasts as $toast): ?>
            <?= toast(
                $toast['type'] ?? 'neutral',
                $toast['title'] ?? '',
                $toast['message'] ?? '',
                $toast['closable'] ?? true,
                $toast['timeout'] ?? 0
            ) ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Alert component
 * 
 * @param string $type Type of alert (neutral, success, warning, error)
 * @param string $message Alert message
 * @param bool $closable Whether alert can be closed
 * @param bool $withBorder Add a colored border to the alert
 * @return string HTML output
 */
function alert($type = 'neutral', $message = '', $closable = true, $withBorder = false) {
    $alertClasses = [
        'neutral' => 'alert-neutral',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error' => 'alert-error',
        'info' => 'alert-info'
    ];
    
    $icons = [
        'neutral' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
        'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        'warning' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        'info' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    ];
    
    $class = isset($alertClasses[$type]) ? $alertClasses[$type] : $alertClasses['neutral'];
    $icon = isset($icons[$type]) ? $icons[$type] : $icons['neutral'];
    $borderClass = $withBorder ? 'alert-with-border' : '';
    
    ob_start(); ?>
    <div class="alert <?= $class ?> <?= $borderClass ?>" role="alert">
        <div class="alert-icon"><?= $icon ?></div>
        <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        <?php if ($closable): ?>
        <button type="button" class="alert-close" aria-label="Close" onclick="this.parentElement.remove()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Dialog/modal component
 * 
 * @param string $id Unique ID for the dialog
 * @param string $title Dialog title
 * @param string $content Dialog content
 * @param array $buttons Array of button config arrays [['label' => 'Ok', 'type' => 'primary', 'action' => 'closeDialog()'], ...]
 * @param string $type Dialog type (default, positive, error, action)
 * @param string|null $icon Icon HTML for the dialog (if null, uses default for type)
 * @return string HTML output
 */
function dialog($id, $title = '', $content = '', $buttons = [], $type = 'default', $icon = null) {
    $dialogTypes = [
        'default' => [
            'class' => 'dialog-default',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        ],
        'positive' => [
            'class' => 'dialog-positive',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        ],
        'error' => [
            'class' => 'dialog-error',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>'
        ],
        'action' => [
            'class' => 'dialog-action',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'
        ]
    ];
    
    if (!isset($dialogTypes[$type])) {
        $type = 'default';
    }
    
    $dialogClass = $dialogTypes[$type]['class'];
    $dialogIcon = $icon !== null ? $icon : $dialogTypes[$type]['icon'];
    
    ob_start(); ?>
    <div id="<?= htmlspecialchars($id) ?>" class="dialog-overlay" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($id) ?>-title" style="display: none;">
        <div class="dialog <?= $dialogClass ?>">
            <div class="dialog-header">
                <span id="<?= htmlspecialchars($id) ?>-title" class="dialog-title"><?= htmlspecialchars($title) ?></span>
                <button type="button" class="dialog-close" aria-label="Close" onclick="document.getElementById('<?= htmlspecialchars($id) ?>').style.display = 'none';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            
            <div class="dialog-body">
                <?php if ($dialogIcon): ?>
                <div class="dialog-icon">
                    <?= $dialogIcon ?>
                </div>
                <?php endif; ?>
                <div class="dialog-content">
                    <?= $content ?>
                </div>
            </div>
            
            <?php if (!empty($buttons)): ?>
            <div class="dialog-footer">
                <?php foreach ($buttons as $button): ?>
                    <?php 
                    $btnType = isset($button['type']) ? $button['type'] : 'secondary';
                    $btnClass = 'btn btn-' . $btnType;
                    $btnAction = isset($button['action']) ? $button['action'] : 'document.getElementById(\'' . htmlspecialchars($id) . '\').style.display = \'none\';';
                    ?>
                    <button 
                        type="button" 
                        class="<?= $btnClass ?>"
                        onclick="<?= $btnAction ?>">
                        <?= htmlspecialchars($button['label'] ?? 'Button') ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Opens a dialog by ID (JavaScript function)
 * 
 * @param string $dialogId ID of dialog to open
 * @return string JavaScript code to execute
 */
function openDialog($dialogId) {
    return "document.getElementById('" . htmlspecialchars($dialogId) . "').style.display = 'flex';";
}

/**
 * Chip/tag component
 * 
 * @param string $text Chip text
 * @param string $type Chip style (outlined, smooth, selected, tech)
 * @param string|null $icon Optional icon HTML
 * @param bool $selectable Whether chip is selectable/toggleable
 * @param string $color Optional color name (tech chips only)
 * @return string HTML output
 */
function chip($text, $type = 'outlined', $icon = null, $selectable = false, $color = null) {
    $chipClass = 'chip chip-' . $type;
    
    if ($selectable) {
        $chipClass .= ' chip-selectable';
    }
    
    if ($color && $type == 'tech') {
        $chipClass .= ' chip-color-' . $color;
    }
    
    ob_start(); ?>
    <div class="<?= $chipClass ?>" <?= $selectable ? 'onclick="this.classList.toggle(\'selected\')"' : '' ?>>
        <?php if ($icon): ?>
            <span class="chip-icon"><?= $icon ?></span>
        <?php endif; ?>
        <span class="chip-text"><?= htmlspecialchars($text) ?></span>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Chip group component
 * 
 * @param array $chips Array of chip config arrays
 * @param string $title Optional group title
 * @param string $type Style for all chips in group
 * @return string HTML output
 */
function chipGroup($chips = [], $title = null, $type = 'outlined') {
    ob_start(); ?>
    <div class="chip-group">
        <?php if ($title): ?>
            <div class="chip-group-title"><?= htmlspecialchars($title) ?></div>
        <?php endif; ?>
        <div class="chip-container">
            <?php foreach ($chips as $chipConfig): ?>
                <?= chip(
                    $chipConfig['text'] ?? '',
                    $chipConfig['type'] ?? $type,
                    $chipConfig['icon'] ?? null,
                    $chipConfig['selectable'] ?? false,
                    $chipConfig['color'] ?? null
                ) ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Filter component
 * 
 * @param string $id Unique ID for the filter
 * @param array $filterGroups Array of filter group configs
 * @param int|null $resultCount Optional number of results to show
 * @return string HTML output
 */
function filter($id, $filterGroups = [], $resultCount = null) {
    ob_start(); ?>
    <div id="<?= htmlspecialchars($id) ?>" class="filter-container">
        <div class="filter-header">
            <button type="button" class="filter-close" aria-label="Close" onclick="document.getElementById('<?= htmlspecialchars($id) ?>').style.display = 'none';">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <?php foreach ($filterGroups as $group): ?>
            <div class="filter-group">
                <h3 class="filter-group-title"><?= htmlspecialchars($group['title'] ?? '') ?></h3>
                
                <?php if (isset($group['type']) && $group['type'] === 'range'): ?>
                    <div class="filter-range">
                        <div class="filter-range-values">
                            <span><?= htmlspecialchars($group['min'] ?? 0) ?></span>
                            <span><?= htmlspecialchars($group['max'] ?? 100) ?></span>
                        </div>
                        <input 
                            type="range" 
                            min="<?= htmlspecialchars($group['min'] ?? 0) ?>" 
                            max="<?= htmlspecialchars($group['max'] ?? 100) ?>" 
                            value="<?= htmlspecialchars($group['value'] ?? $group['min'] ?? 0) ?>"
                            class="filter-range-slider"
                        >
                    </div>
                <?php elseif (isset($group['type']) && $group['type'] === 'dual-range'): ?>
                    <div class="filter-range dual-range">
                        <div class="filter-range-values">
                            <span class="min-value">$<?= htmlspecialchars($group['minValue'] ?? $group['min'] ?? 0) ?></span>
                            <span class="max-value">$<?= htmlspecialchars($group['maxValue'] ?? $group['max'] ?? 100) ?></span>
                        </div>
                        <div class="filter-range-slider-container">
                            <div class="filter-range-track"></div>
                            <div class="filter-range-fill"></div>
                            <input 
                                type="range" 
                                min="<?= htmlspecialchars($group['min'] ?? 0) ?>" 
                                max="<?= htmlspecialchars($group['max'] ?? 100) ?>" 
                                value="<?= htmlspecialchars($group['minValue'] ?? $group['min'] ?? 0) ?>"
                                class="filter-range-slider min-slider"
                            >
                            <input 
                                type="range" 
                                min="<?= htmlspecialchars($group['min'] ?? 0) ?>" 
                                max="<?= htmlspecialchars($group['max'] ?? 100) ?>" 
                                value="<?= htmlspecialchars($group['maxValue'] ?? $group['max'] ?? 100) ?>"
                                class="filter-range-slider max-slider"
                            >
                        </div>
                    </div>
                <?php elseif (isset($group['options'])): ?>
                    <div class="filter-options">
                        <?php foreach ($group['options'] as $option): ?>
                            <label class="filter-option">
                                <input 
                                    type="<?= $group['multiple'] ?? true ? 'checkbox' : 'radio' ?>"
                                    name="<?= htmlspecialchars($group['name'] ?? '') ?>"
                                    value="<?= htmlspecialchars($option['value'] ?? '') ?>"
                                    <?= isset($option['selected']) && $option['selected'] ? 'checked' : '' ?>
                                >
                                <?php if (isset($option['color'])): ?>
                                    <span class="filter-color-dot" style="background-color: <?= htmlspecialchars($option['color']) ?>"></span>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($option['label'] ?? '') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($resultCount !== null): ?>
            <div class="filter-footer">
                <button type="button" class="btn btn-primary filter-apply">
                    Show <?= htmlspecialchars($resultCount) ?> Results
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Breakpoint indicator component for responsive design visualization
 * 
 * @return string HTML output
 */
function breakpointIndicator() {
    ob_start(); ?>
    <div class="breakpoint-indicator">
        <div class="breakpoint-container">
            <h2>Web Breakpoints</h2>
            <p>(For Responsive Website)</p>
            
            <div class="breakpoint-grid">
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Mobile</h3>
                        <p>&le; 480px</p>
                    </div>
                    <div class="breakpoint-icon mobile-portrait"></div>
                </div>
                
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Mobile</h3>
                        <p>&le; 768px</p>
                        <p class="breakpoint-mode">(Landscape)</p>
                    </div>
                    <div class="breakpoint-icon mobile-landscape"></div>
                </div>
                
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Tablet</h3>
                        <p>&le; 834px</p>
                    </div>
                    <div class="breakpoint-icon tablet-portrait"></div>
                </div>
                
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Tablet</h3>
                        <p>&le; 1024px</p>
                        <p class="breakpoint-mode">(Landscape)</p>
                    </div>
                    <div class="breakpoint-icon tablet-landscape"></div>
                </div>
                
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Laptop</h3>
                        <p>&le; 1440px</p>
                    </div>
                    <div class="breakpoint-icon laptop"></div>
                </div>
                
                <div class="breakpoint-item">
                    <div class="breakpoint-info">
                        <h3>Desktop</h3>
                        <p>&le; 1440px</p>
                        <p class="breakpoint-mode">(Landscape)</p>
                    </div>
                    <div class="breakpoint-icon desktop"></div>
                </div>
            </div>
            
            <button class="btn btn-outline breakpoint-save">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                Save it for Later
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
