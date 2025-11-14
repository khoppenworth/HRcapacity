# HRcapacity

HRcapacity is a full-stack application composed of a Laravel API backend and a React + Vite frontend. This document explains how to deploy both services for a production environment.

## Repository structure

- `backend/` – Laravel 10+ API providing business logic, authentication, and database access.
- `frontend/` – React single-page application bundled with Vite that consumes the API.

## Deployment guide

### 1. Prerequisites

- PHP 8.1 or newer with extensions required by Laravel (`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`).
- Composer.
- Node.js 18+ and npm (for building the frontend).
- MySQL 8+ (or MariaDB 10.6+) database server.
- Web server capable of serving PHP applications (e.g., Nginx or Apache) and static assets.

### 2. Clone the repository

```bash
# On your deployment machine
git clone https://github.com/your-org/HRcapacity.git
cd HRcapacity
```

### 3. Configure the backend

1. Install PHP dependencies:
   ```bash
   cd backend
   composer install --no-dev --optimize-autoloader
   ```
2. Copy the environment template and adjust settings:
   ```bash
   cp .env.example .env
   ```
   Update at least the following keys:
   - `APP_NAME`, `APP_URL`
   - `APP_ENV=production` and `APP_DEBUG=false`
   - Database credentials: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - Any mail, queue, or storage configuration used by your deployment.
3. Generate the Laravel application key:
   ```bash
   php artisan key:generate
   ```
4. Run database migrations (and seed data if required):
   ```bash
   php artisan migrate --force
   # Optional: php artisan db:seed --force
   ```
5. Cache optimized configuration and routes:
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```
6. Ensure correct file permissions for the web server user (example for Linux environments):
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R ug+rwx storage bootstrap/cache
   ```
7. Configure your web server to serve `backend/public` as the document root and run PHP through FPM or mod_php. Typical Nginx location block:
   ```nginx
   root /var/www/HRcapacity/backend/public;
   index index.php;
   try_files $uri $uri/ /index.php?$query_string;
   ```

### 4. Configure the frontend

1. Install JavaScript dependencies and build the production bundle:
   ```bash
   cd ../frontend
   npm install
   npm run build
   ```
   The optimized assets will be generated in `frontend/dist`.
2. Copy the frontend environment template (if present) and point the API URL to the deployed backend:
   ```bash
   cp .env.example .env  # create the file if it doesn't exist
   echo "VITE_API_URL=https://your-backend-domain/api/v1" > .env
   ```
   Re-run `npm run build` whenever the API URL or other environment variables change.
3. Serve the `dist/` directory using your preferred static hosting solution:
   - **Via Nginx/Apache:** configure a virtual host that points to the `frontend/dist` directory.
   - **Via CDN or object storage:** upload the contents of `dist/` and configure caching/SSL as appropriate.

### 5. Connect frontend and backend

- Confirm the frontend's `VITE_API_URL` matches the public URL of the backend API (e.g., `https://api.example.com/api/v1`).
- Ensure CORS is configured on the backend (`config/cors.php`) to allow requests from the frontend domain.
- If using HTTPS, make sure both the API and frontend are served over TLS and that the `.env` values (such as `APP_URL`) use `https://`.

### 6. Background workers & scheduling (optional)

If your deployment uses queues, jobs, or scheduled tasks, configure the following after deployment:

- Start the queue worker: `php artisan queue:work --daemon`
- Add a cron entry to run the Laravel scheduler every minute:
  ```cron
  * * * * * www-data php /var/www/HRcapacity/backend/artisan schedule:run >> /dev/null 2>&1
  ```

### 7. Health checks and monitoring

- Configure health endpoints (e.g., `GET /api/v1/health`) in your load balancer.
- Monitor log files in `storage/logs/laravel.log` and set up centralized logging/alerting.
- Keep track of application metrics such as response times, queue lengths, and database performance.

### 8. Ongoing maintenance

- Pull updates and re-run migrations as needed:
  ```bash
  git pull origin main
  composer install --no-dev --optimize-autoloader
  php artisan migrate --force
  npm install
  npm run build
  ```
- Clear caches after configuration changes: `php artisan config:cache`.
- Regularly back up your database and `.env` file.

## Additional resources

- [Laravel deployment documentation](https://laravel.com/docs/deployment)
- [Vite deployment guide](https://vitejs.dev/guide/build.html)

