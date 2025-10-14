
<?php
/**
 * Twig template example
 */

// Include Twig library (normally you'd use Composer for this)
// require_once 'vendor/autoload.php';

// For demo purposes, we'll mock Twig functionality
function renderTwigTemplate($templateName, $context = []) {
    $templateContent = file_get_contents('templates/' . $templateName . '.twig');
    
    // Extremely basic Twig-like variable substitution
    foreach ($context as $key => $value) {
        if (is_string($value) || is_numeric($value)) {
            $templateContent = str_replace('{{ ' . $key . ' }}', $value, $templateContent);
        }
    }
    
    // Basic if statement handling
    $templateContent = preg_replace_callback(
        '/{% if ([a-zA-Z0-9_]+) %}(.+?){% endif %}/s',
        function($matches) use ($context) {
            $variable = trim($matches[1]);
            $content = $matches[2];
            return isset($context[$variable]) && $context[$variable] ? $content : '';
        },
        $templateContent
    );
    
    // Basic for loop handling
    $templateContent = preg_replace_callback(
        '/{% for ([a-zA-Z0-9_]+) in ([a-zA-Z0-9_]+) %}(.+?){% endfor %}/s',
        function($matches) use ($context) {
            $itemVar = trim($matches[1]);
            $arrayVar = trim($matches[2]);
            $content = $matches[3];
            $result = '';
            
            if (isset($context[$arrayVar]) && is_array($context[$arrayVar])) {
                foreach ($context[$arrayVar] as $item) {
                    $itemContent = $content;
                    if (is_string($item) || is_numeric($item)) {
                        $itemContent = str_replace('{{ ' . $itemVar . ' }}', $item, $itemContent);
                    }
                    $result .= $itemContent;
                }
            }
            
            return $result;
        },
        $templateContent
    );
    
    return $templateContent;
}

// Sample data for the template
$pageData = [
    'title' => 'Twig Template Example',
    'username' => 'User123',
    'isLoggedIn' => true,
    'items' => [
        'Item 1',
        'Item 2',
        'Item 3',
        'Item 4',
        'Item 5'
    ]
];

// Render the template
$renderedContent = renderTwigTemplate('example', $pageData);

// Output the rendered template
echo $renderedContent;
?>
