# CODE STRUCTURE

P.I.M.P is organized for modularity, maintainability, and scalability. Below is an overview of the main directories and their purposes:

## Root Files
- `index.php`: Entry point for the application.
- `composer.json`, `composer.lock`: PHP dependencies.
- `Dockerfile`, `docker-compose.yml`: Containerization setup.
- `Makefile`: Build and utility commands.

## Main Directories
- `admin/`: Admin dashboard, controllers, models, and views for platform management.
- `config/`: Configuration files (app, database, environment).
- `controller/`: Main application controllers (business logic).
- `docs/`: Documentation (public and developer).
- `include/`: Shared PHP includes and helpers.
- `model/`: Data models and ORM logic.
- `public/`: Publicly accessible assets and entry points.
- `scripts/`: Utility and setup scripts.
- `service/`: Service classes (API, authentication, etc.).
- `static/`: Static assets (JS, images, fonts).
- `styles/`: CSS and theme files.
- `tmp/`: Temporary files and cache.
- `util/`: Utility classes and helpers.
- `vendor/`: Composer dependencies.
- `view/`: UI components, pages, and templates.

## Example: Using a Component
To use a header component in a PHP view:
```php
use PIMP\Views\Components\Headers;
echo Headers::headerOne(['title' => 'Welcome', 'navItems' => $navItems]);
```

## Example: Collapsible Navigation (JS)
See `static/js/HEADERS.JS` for toggling navigation menus:
```js
var navbarToggle = document.querySelector('.navbar-toggle');
navbarToggle.addEventListener('click', function() {
    // Toggle menu
});
```

## Tools & Utilities
- `quick-setup.sh`: Fast Docker setup.
- `docker-manager.sh`: Manage containers.
- `test-connections.sh`: Test DB and service connections.

## Extending the Platform
- Add new models in `model/`
- Create new views in `view/pages/`
- Register new routes in `controller/`

See other files in `docs/dev/` for more details.
