<?php
/**
 * P.I.M.P - Comprehensive Error Page
 * Handles all HTTP error codes (4xx and 5xx)
 */
use PIMP\Core\Config;

// Initialize Config
Config::init();

// Get error details from parameters or use defaults
$errorCode = $errorCode ?? http_response_code() ?? 500;
$errorTitle = $errorTitle ?? 'Error';
$errorMessage = $errorMessage ?? 'An unexpected error occurred.';

// Define comprehensive error information
$errorDefinitions = [
    // Success (2xx) - for completeness
    200 => [
        'title' => 'Success',
        'message' => 'The request was successful.',
        'icon' => 'fa-check-circle',
        'color' => '#4caf50',
        'category' => 'success'
    ],
    201 => [
        'title' => 'Created',
        'message' => 'The resource has been successfully created.',
        'icon' => 'fa-check-circle',
        'color' => '#4caf50',
        'category' => 'success'
    ],
    
    // Client Errors (4xx)
    400 => [
        'title' => 'Bad Request',
        'message' => 'The server cannot understand your request due to incorrect syntax. Please check your input and try again.',
        'icon' => 'fa-exclamation-circle',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => 'Authentication is required to access this resource. Please log in and try again.',
        'icon' => 'fa-lock',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    403 => [
        'title' => 'Forbidden',
        'message' => 'You do not have permission to access this resource. Contact an administrator if you believe this is an error.',
        'icon' => 'fa-ban',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    404 => [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for does not exist or has been moved. Please check the URL or return to the homepage.',
        'icon' => 'fa-exclamation-triangle',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    405 => [
        'title' => 'Method Not Allowed',
        'message' => 'The HTTP method used is not allowed for this resource.',
        'icon' => 'fa-times-circle',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    408 => [
        'title' => 'Request Timeout',
        'message' => 'The server timed out waiting for your request. Please try again.',
        'icon' => 'fa-hourglass-end',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    409 => [
        'title' => 'Conflict',
        'message' => 'The request conflicts with the current state of the resource.',
        'icon' => 'fa-code-branch',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    410 => [
        'title' => 'Gone',
        'message' => 'The requested resource is no longer available and will not be available again.',
        'icon' => 'fa-trash',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    413 => [
        'title' => 'Payload Too Large',
        'message' => 'The request is larger than the server is willing or able to process.',
        'icon' => 'fa-weight-hanging',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    415 => [
        'title' => 'Unsupported Media Type',
        'message' => 'The media format of the requested data is not supported by the server.',
        'icon' => 'fa-file-alt',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    429 => [
        'title' => 'Too Many Requests',
        'message' => 'You have sent too many requests in a given amount of time. Please slow down and try again later.',
        'icon' => 'fa-hourglass-half',
        'color' => '#ffa726',
        'category' => 'client_error'
    ],
    
    // Server Errors (5xx)
    500 => [
        'title' => 'Internal Server Error',
        'message' => 'Something went wrong on our end. Our team has been notified. Please try again later.',
        'icon' => 'fa-server',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    501 => [
        'title' => 'Not Implemented',
        'message' => 'The server does not support the functionality required to fulfill this request.',
        'icon' => 'fa-tools',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    502 => [
        'title' => 'Bad Gateway',
        'message' => 'The server received an invalid response from an upstream server. Please try again in a few moments.',
        'icon' => 'fa-network-wired',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => 'The server is temporarily unable to handle your request, often due to maintenance or overload. Please try again later.',
        'icon' => 'fa-tools',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    504 => [
        'title' => 'Gateway Timeout',
        'message' => 'The server did not receive a timely response from an upstream server. Please try again.',
        'icon' => 'fa-clock',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    505 => [
        'title' => 'HTTP Version Not Supported',
        'message' => 'The server does not support the HTTP protocol version used in your request.',
        'icon' => 'fa-code',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    507 => [
        'title' => 'Insufficient Storage',
        'message' => 'The server is unable to store the representation needed to complete the request.',
        'icon' => 'fa-hdd',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    508 => [
        'title' => 'Loop Detected',
        'message' => 'The server detected an infinite loop while processing the request.',
        'icon' => 'fa-sync-alt',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ],
    511 => [
        'title' => 'Network Authentication Required',
        'message' => 'You need to authenticate to gain network access.',
        'icon' => 'fa-wifi',
        'color' => '#ff6b6b',
        'category' => 'server_error'
    ]
];

// Get error info or use custom values
$errorInfo = $errorDefinitions[$errorCode] ?? [
    'title' => $errorTitle,
    'message' => $errorMessage,
    'icon' => 'fa-exclamation-triangle',
    'color' => '#ff6b6b',
    'category' => $errorCode >= 500 ? 'server_error' : 'client_error'
];

// Override with custom values if provided
if (isset($errorTitle) && $errorTitle !== 'Error') {
    $errorInfo['title'] = $errorTitle;
}
if (isset($errorMessage) && $errorMessage !== 'An unexpected error occurred.') {
    $errorInfo['message'] = $errorMessage;
}

$isServerError = $errorInfo['category'] === 'server_error';
$isSuccess = $errorInfo['category'] === 'success';

// Set HTTP response code
http_response_code($errorCode);
?>
<!DOCTYPE html>
<html lang="en" data-theme="purple1">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $errorCode ?> - <?= htmlspecialchars($errorInfo['title']) ?> | P.I.M.P</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= Config::styleUrl('theme.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .error-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .error-header h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .error-header a {
            color: white;
            text-decoration: none;
        }
        
        .error-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .error-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 650px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-icon {
            font-size: 5rem;
            color: <?= $errorInfo['color'] ?>;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            line-height: 1;
        }
        
        .error-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-category {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .category-client_error {
            background: rgba(255, 167, 38, 0.1);
            color: #f57c00;
        }
        
        .category-server_error {
            background: rgba(255, 107, 107, 0.1);
            color: #d32f2f;
        }
        
        .category-success {
            background: rgba(76, 175, 80, 0.1);
            color: #388e3c;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .button-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .button-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .button-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .button-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .button-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .error-help {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .error-help p {
            color: #95a5a6;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .error-help a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .error-help a:hover {
            text-decoration: underline;
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            text-align: left;
        }
        
        .error-details h3 {
            font-size: 1rem;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .error-details ul {
            list-style: none;
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.8;
        }
        
        .error-details li {
            padding-left: 1.5rem;
            position: relative;
        }
        
        .error-details li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .error-content {
                padding: 2rem 1.5rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="error-header">
        <h1><a href="<?= Config::url('/') ?>">P.I.M.P Business Repository</a></h1>
    </header>
    
    <main class="error-main">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas <?= $errorInfo['icon'] ?>"></i>
            </div>
            
            <div class="error-code"><?= $errorCode ?></div>
            
            <span class="error-category category-<?= $errorInfo['category'] ?>">
                <?= str_replace('_', ' ', $errorInfo['category']) ?>
            </span>
            
            <h2 class="error-title"><?= htmlspecialchars($errorInfo['title']) ?></h2>
            <p class="error-message"><?= htmlspecialchars($errorInfo['message']) ?></p>
            
            <div class="error-actions">
                <a href="<?= Config::url('/') ?>" class="button button-primary">
                    <i class="fas fa-home"></i>
                    Return to Homepage
                </a>
                
                <?php if ($errorCode === 404): ?>
                    <a href="<?= Config::url('/businesses') ?>" class="button button-outline">
                        <i class="fas fa-search"></i>
                        Browse Businesses
                    </a>
                <?php elseif ($isServerError): ?>
                    <a href="javascript:location.reload()" class="button button-outline">
                        <i class="fas fa-redo"></i>
                        Try Again
                    </a>
                <?php elseif ($errorCode === 401): ?>
                    <a href="<?= Config::url('/login') ?>" class="button button-outline">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php endif; ?>
                
                <a href="javascript:history.back()" class="button button-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Go Back
                </a>
            </div>
            
            <?php if ($errorCode === 404): ?>
            <div class="error-details">
                <h3><i class="fas fa-info-circle"></i> What you can do:</h3>
                <ul>
                    <li>Check the URL for typos</li>
                    <li>Use the search feature to find what you're looking for</li>
                    <li>Browse our business directory</li>
                    <li>Return to the homepage and start over</li>
                </ul>
            </div>
            <?php elseif ($isServerError): ?>
            <div class="error-details">
                <h3><i class="fas fa-wrench"></i> What's happening:</h3>
                <ul>
                    <li>Our servers are experiencing technical difficulties</li>
                    <li>Our team has been automatically notified</li>
                    <li>This is usually temporary - please try again soon</li>
                    <li>If the problem persists, contact our support team</li>
                </ul>
            </div>
            <?php elseif ($errorCode === 429): ?>
            <div class="error-details">
                <h3><i class="fas fa-clock"></i> Rate limit information:</h3>
                <ul>
                    <li>You've exceeded the maximum number of requests</li>
                    <li>Please wait a few minutes before trying again</li>
                    <li>Consider spacing out your requests</li>
                    <li>Contact us if you need higher limits</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="error-help">
                <p>
                    <?php if ($errorCode === 404): ?>
                        Looking for something specific? <a href="<?= Config::url('/contact') ?>">Contact us</a> for help.
                    <?php elseif ($errorCode === 401 || $errorCode === 403): ?>
                        Need access to this resource? <a href="<?= Config::url('/contact') ?>">Request access</a> from our team.
                    <?php else: ?>
                        If this problem persists, please <a href="<?= Config::url('/contact') ?>">contact support</a>.
                    <?php endif; ?>
                </p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    Error Reference: <?= $errorCode ?>-<?= date('YmdHis') ?>-<?= substr(md5(uniqid()), 0, 8) ?>
                </p>
            </div>
        </div>
    </main>
    
    <script>
        // Log error for debugging (if in development mode)
        <?php if (Config::isDevelopment()): ?>
        console.error('Error <?= $errorCode ?>: <?= addslashes($errorInfo['title']) ?>');
        <?php endif; ?>
        
        // Auto-retry for certain server errors after 5 seconds
        <?php if (in_array($errorCode, [502, 503, 504])): ?>
        let countdown = 10;
        const retryTimer = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(retryTimer);
                location.reload();
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>