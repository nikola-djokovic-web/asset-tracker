# 🛡️ Asset & Inventory Tracker (Multi-Tenant SaaS API)

A robust, production-ready Multi-Tenant SaaS RESTful API for enterprise asset and inventory management built with **Laravel 11**, **PostgreSQL**, and **Pest PHP**. Designed with strict data isolation, polymorphic domain modeling, and unified API response architecture.

---

## ✨ Key Features

* **Strict Multi-Tenancy Architecture**: Automatic tenant-scoped data isolation using custom Model Traits, Global Scopes, and Laravel Policies (`tenant_id`).
* **Polymorphic Asset Modeling**: Dynamic details layer for hardware devices (`serial_number`, custom hardware specs) and software licenses (`license_key`, `seats`, expiration tracking).
* **Robust API Authorization**: Granular control via Laravel Sanctum bearer tokens and strict Policy-based authorization guards.
* **Unified Exception & API Handling**: Centralized JSON exception mapping in `bootstrap/app.php` ensuring consistent response structures for validation (`422`), authorization (`403`), and route resolution (`404`) errors.
* **Comprehensive Test Suite**: 100% test-driven workflows built with **Pest PHP**, covering multi-tenant data leakage prevention, factory seeding (with ULIDs), and polymorphic payload assertions.

---

## 🛠️ Tech Stack & Dependencies

* **Framework**: [Laravel 11](https://laravel.com/) (PHP 8.3+)
* **Database**: PostgreSQL (Production) / SQLite In-Memory (Automated Testing)
* **Authentication**: Laravel Sanctum (API Tokens)
* **Primary Keys**: ULIDs (`Universally Unique Lexicographically Sortable Identifiers`) for collision-free data scaling and security.
* **Testing Framework**: [Pest PHP](https://pestphp.com/)

---

## 📐 Architecture & Database Model

+-------------------------------------------------------------------------+
|                                 TENANT                                  |
+-------------------------------------------------------------------------+
|                                      |                     |
v                                      v                     v
+--------------+                      +---------------+     +---------------+
|    USERS     |                      |  CATEGORIES   |     | ORGANIZATIONS |
+--------------+                      +---------------+     +---------------+
|                                      |                     |
+--------------------+                 |                     |
v                 v                     v
+-----------------------------------------------+
|                    ASSETS                     |
+-----------------------------------------------+
|  id (ULID)                                    |
|  tenant_id                                    |
|  itemable_type (HardwareDetail / License)     |
|  itemable_id                                  |
+-----------------------------------------------+
| (Polymorphic)
+----------------+----------------+
|                                 |
v                                 v
+--------------------+             +-------------------+
|  HARDWARE_DETAILS  |             |  LICENSE_DETAILS  |
+--------------------+             +-------------------+


---

## 🚀 Getting Started

### Prerequisites
* PHP >= 8.3 with SQLite & PostgreSQL extensions
* Composer
* PostgreSQL instance running locally or via Docker

### Installation Steps

1. **Clone the repository:**
   ```bash
   git clone ...
   cd asset-tracker-api

    Install PHP dependencies:
    Bash

    composer install

    Configure Environment Variables:
    Bash

    cp .env.example .env
    php artisan key:generate

    Set up your PostgreSQL database credentials in .env:
    Code snippet

    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=asset_tracker
    DB_USERNAME=your_username
    DB_PASSWORD=your_password

    Run Database Migrations & Seeders:
    Bash

    php artisan migrate --seed

    Start the Local Development Server:
    Bash

    php artisan serve

    The API will be accessible at http://127.0.0.1:8000/api.

🧪 Running Automated Tests

The testing suite utilizes Pest PHP with an isolated SQLite in-memory database configuration to execute tests rapidly without affecting your local database.
Bash

# Run the complete test suite
php artisan test

# Run a specific test feature
php artisan test --filter=TenantIsolationTest
php artisan test --filter=AssetPolymorphismTest

🔒 Security & Data Isolation Principles

    Single-DB Multi-Tenancy: Every database table is bound to tenant_id.

    BelongsToTenant Trait: Automatically applies global scopes to queries ensuring users can only read and mutate records owned by their authenticated tenant_id.

    Explicit Authorization Gateways: Requests are authorized through Policy guards prior to controller dispatching.

📄 License

This project is open-sourced software licensed under the MIT license.