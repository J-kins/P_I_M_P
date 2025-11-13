# P.I.M.P Codebase Summary

## Overview
P.I.M.P (Business Repository Platform based on Trust) is a modular PHP application with a modern frontend and Dockerized deployment. It provides business listings, user reviews, admin controls, and supports both native and containerized environments. The backend is organized using MVC principles, and the frontend leverages JavaScript libraries such as D3, Leaflet, and Mermaid for visualization.

## Key Features
- User and business authentication (with email verification)
- Business repository and management
- User reviews and complaint handling
- Admin dashboard with moderation tools
- Modular service and utility layers
- Docker and Makefile support for flexible deployment
- Automated testing with PHPUnit

## Main Directories
- `admin/` – Admin controllers, models, services, and views
- `config/` – Application and database configuration
- `controller/` – Main application controllers (e.g., Auth, Sitemap)
- `docs/` – Documentation (usage, Docker, Makefile, changelogs)
- `model/` – Data models (User, Business, Review, etc.)
- `public/` – Web root and static assets
- `scripts/` – Shell scripts for setup and management
- `service/` – Core business logic and services (authentication, email, database, etc.)
- `static/` – Frontend JS, CSS, and visualization assets
- `styles/` – Stylesheets and themes
- `util/` – Utility functions
- `vendor/` – Composer dependencies
- `view/` – UI components and page templates

## Technology Stack
- **Backend:** PHP 8.1+, Composer, MVC structure
- **Frontend:** JavaScript (D3, Leaflet, Mermaid, Lottie, Socket.IO)
- **Database:** MySQL (default), with support for MongoDB, Redis, SQLite
- **DevOps:** Docker, Makefile, shell scripts
- **Testing:** PHPUnit

## Setup & Usage
- Use `make setup` or `./scripts/quick-setup.sh` for environment setup
- Supports full Docker, native, or hybrid deployment (see `docs/DOCKERSETUP.md`)
- Run tests with `make test` or via CI

## Contribution & Documentation
- Follow PSR standards for PHP
- Document new features in `docs/dev/`
- See `docs/USAGE-EXAMPLE.md` for authentication setup
- Changelogs in `docs/changelogs/`

---
For more details, see the main `README.MD` and documentation in the `docs/` folder.
