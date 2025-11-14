# HRassess v4 Backend

## Prerequisites
- PHP 8.1+
- Composer
- MySQL or MariaDB instance

## Installation
1. Install dependencies:
   ```bash
   composer install
   ```
2. Copy the example environment and update values:
   ```bash
   cp .env.example .env
   ```
3. Generate application key:
   ```bash
   php artisan key:generate
   ```
4. Configure database credentials in `.env`.

## Database Migrations
Run the migrations:
```bash
php artisan migrate
```

## Running the Development Server
Start the API server:
```bash
php artisan serve
```
The API will be available at `http://localhost:8000/api/v1`.

## Testing
Run the PHPUnit test suite:
```bash
php artisan test
```
