<?php

/**
 * Component loader
 * 
 * Loads all component files and makes them globally available
 */

// Define component directories
$component_dirs = [
    __DIR__ . '/components',
    // Add more component directories as needed
];

// Load all component files
foreach ($component_dirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            require_once $file;
        }
    }
}


/**
 * Get component override from current theme if available
 * 
 * @param string $component Component name (e.g., 'header', 'footer')
 * @param array $params Parameters to pass to the component
 * @return string HTML output
 */

function get_themed_component($component, $params = []) {
    $theme = get_active_theme();
    $theme_component = __DIR__ . "/components/templates/{$theme}/{$component}.php";
    
    if (file_exists($theme_component)) {
        // Load theme-specific component
        ob_start();
        extract($params);
        include $theme_component;
        return ob_get_clean();
    }
    
    // Fall back to default component function
    $function_name = $component;
    if (function_exists($function_name)) {
        return call_user_func($function_name, $params);
    }
    
    return ''; // Component not found
}

/*
/**
 * Get active theme name
 * 
 * @return string Theme name
 */
/*
function get_active_theme() {
    // Get theme from session if set
    if (isset($_SESSION['user_theme'])) {
        return $_SESSION['user_theme'];
    }
    
    // Default theme
    return 'purple1';
}
*/

/**
 * Generate theme selector component
 * 
 * @return string HTML output for theme selector
 */
function theme_selector() {
    // Available themes
    $themes = [
        'purple1' => 'Purple',
        'oceanic' => 'Oceanic', 
        'slate' => 'Slate',
        'coffee' => 'Coffee',
        'grayscale' => 'Grayscale',
        'berry' => 'Berry'
    ];
    
    $active_theme = get_active_theme();
    
    ob_start(); ?>
    <div class="theme-selector">
        <h4>Choose Theme</h4>
        <div class="theme-options">
            <?php foreach ($themes as $id => $name): ?>
            <button 
                class="theme-option <?= ($active_theme === $id) ? 'active' : '' ?>" 
                data-theme="<?= htmlspecialchars($id) ?>"
                title="<?= htmlspecialchars($name) ?> Theme">
                <span class="theme-swatch" style="--theme: <?= htmlspecialchars($id) ?>"></span>
                <?= htmlspecialchars($name) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>