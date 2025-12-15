# Setting Up Your .env File - Step by Step

## Step 1: Generate APP_KEY
Run this in your project directory:
```bash
php artisan key:generate --show
```
Copy the entire output (including `base64:` prefix)

## Step 2: Get Supabase Credentials
Go to your Supabase project dashboard:

### Find Project URL and Keys:
1. Click **Project Settings** (gear icon, bottom left)
2. Click **API** tab
3. You'll see:
   - **Project URL** - Copy this for `SUPABASE_URL` and `DB_HOST`
   - **anon public** - Copy this for `SUPABASE_KEY`
   - **service_role** - Copy this for `SUPABASE_SECRET_KEY`

### Find Database Password:
1. In Project Settings, click **Database**
2. Look for **Connection string** or **Password**
3. If you don't see it, click **Reset password** to create a new one

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

DB_CONNECTION=pgsql
DB_HOST=PASTE_YOUR_HOST_HERE
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=PASTE_YOUR_PASSWORD_HERE

BROADCAST_DRIVER=log
CACHE_DRIVER=file
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

SUPABASE_URL=PASTE_YOUR_PROJECT_URL_HERE
SUPABASE_KEY=PASTE_YOUR_ANON_KEY_HERE
SUPABASE_SECRET_KEY=PASTE_YOUR_SERVICE_ROLE_KEY_HERE
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

**"SQLSTATE[08006]" error?**
- Check DB_HOST is correct (should be `db.xxxxx.supabase.co`)
- Check DB_PASSWORD is correct
- Check your Supabase project is active

**"Connection refused"?**
- Make sure you're using the correct host
- Verify your Supabase project is running

**"Authentication failed"?**
- Double-check DB_USERNAME is `postgres`
- Verify DB_PASSWORD matches Supabase

## Example Filled .env (for reference)
```env
APP_NAME=JobFilter
APP_ENV=local
APP_KEY=base64:abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db.abcdefghijklmnop.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=MySecurePassword123!

SUPABASE_URL=https://abcdefghijklmnop.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SECRET_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

QUEUE_CONNECTION=sync
CACHE_DRIVER=file
SESSION_DRIVER=file
```

**Note**: Never commit `.env` to GitHub! It's already in `.gitignore`
