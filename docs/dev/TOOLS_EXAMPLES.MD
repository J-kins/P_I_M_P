# TOOLS & EXAMPLES

## Docker
- Build and run containers:
  ```zsh
  docker-compose up --build
  ```
- Stop containers:
  ```zsh
  docker-compose down
  ```
- Use `docker-manager.sh` for advanced management.

## Makefile
- Common commands:
  ```zsh
  make build
  make test
  make clean
  ```

## Scripts
- `quick-setup.sh`: Sets up Docker and dependencies.
- `test-connections.sh`: Verifies DB and service connectivity.

## PHP Usage
- Use components:
  ```php
  use PIMP\Views\Components\Headers;
  echo Headers::headerOne([...]);
  ```
- Access config:
  ```php
  $db = require 'config/database.php';
  ```

## JS Usage
- Collapsible nav:
  ```js
  // See static/js/HEADERS.JS
  ```

## Extending
- Add new pages in `view/pages/`
- Register new services in `service/`
- Add new models in `model/`
