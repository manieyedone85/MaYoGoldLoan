# Gold Loan API

This project is a Laravel API for gold loan operations.

## Requirements

- PHP 7.3 or higher
- Composer
- MySQL database
- Node.js and NPM (optional, for frontend assets)

## Local run

1. Install PHP dependencies
   ```bash
   composer install
   ```

2. Create the environment file
   ```bash
   copy .env.example .env
   ```
   On Linux/macOS:
   ```bash
   cp .env.example .env
   ```

3. Set your local database connection in [.env](.env)

4. Generate the application key
   ```bash
   php artisan key:generate
   ```

5. Run database migrations
   ```bash
   php artisan migrate
   ```

6. Start the application
   ```bash
   php artisan serve
   ```

7. Open the app in the browser
   ```text
   http://127.0.0.1:8000
   ```

### Optional frontend assets

If the project uses front-end build files:

```bash
npm install
npm run dev
```

For a production build, use:

```bash
npm run production
```

## Hostinger deployment

### 1. Upload files
Upload the project files to your hosting account. For Laravel, the public web root should point to the public folder, not the project root.

Recommended structure:
```text
public_html/
└── my-gold-loan-api/
    └── public/
```

Then set the domain document root to:
```text
public_html/my-gold-loan-api/public
```

### 2. Configure environment
Update the production values in [.env](.env):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 3. Run production commands
After uploading the files, run:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 4. Folder permissions
Set writable permissions for storage and cache:

```bash
chmod -R 755 storage bootstrap/cache
```

## Common issues

- If the app shows a blank page or 500 error, check the PHP version and ensure the document root points to the public folder.
- If migrations fail, verify the database credentials in [.env](.env).
- If file uploads do not work, confirm that the storage folder is writable.

## License

This project is licensed under the MIT License.
