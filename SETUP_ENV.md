# Setting Up Your .env File - Step by Step

## Step 1: Generate APP_KEY
Run this in your project directory:
```bash
php artisan key:generate --show
```
Copy the entire output (including `base64:` prefix)

## Step 2: Set Up MySQL Database
Ensure you have MySQL/XAMPP running with a database named `jf_laravel`:

### Using XAMPP:
1. Start Apache and MySQL services
2. Go to http://localhost/phpmyadmin
3. Create database named `jf_laravel`

### Default MySQL Credentials (XAMPP):
- Host: 127.0.0.1
- Port: 3306
- Username: root
- Password: (empty)

## Step 3: Create .env File
Create a new file named `.env` in your project root with:

```env
APP_NAME=JobFilter
APP_ENV=local
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jf_laravel
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Step 4: Test Connection
Run:
```bash
php artisan tinker
>>> DB::connection()->getPdo()
```

If successful, you'll see a PDO object. If error, check your credentials.

## Step 5: Run Migrations
```bash
php artisan migrate
```

## Troubleshooting

**"SQLSTATE[HY000]" error?**
- Check MySQL/XAMPP is running
- Verify database `jf_laravel` exists
- Check DB_HOST is correct (should be `127.0.0.1`)

**"Connection refused"?**
- Make sure MySQL service is started in XAMPP
- Verify port 3306 is available

**"Authentication failed"?**
- Double-check DB_USERNAME is `root`
- Verify DB_PASSWORD is empty for XAMPP default

## Example Filled .env (for reference)
```env
APP_NAME=JobFilter
APP_ENV=local
APP_KEY=base64:abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jf_laravel
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=sync
CACHE_STORE=file
SESSION_DRIVER=file
```

**Note**: Never commit `.env` to GitHub! It's already in `.gitignore`
