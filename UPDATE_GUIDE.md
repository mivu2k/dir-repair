# MEI: Production Server Update Guide

This guide details the step-by-step commands to update your running production system with the latest Spatie-based User Role and Permission Matrix changes.

---

## Step-by-Step Update Sequence

Execute these commands in the terminal of your production server or LXC:

### 1. Navigate and Pull Latest Code
SSH into your server, navigate to the application directory, and pull the latest commits from the `main` branch:
```bash
cd /var/www/mei
git pull origin main
```

### 2. Update Backend Dependencies
Ensure all backend dependencies are optimized for production:
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Rebuild Frontend Assets
Since we made critical upgrades to the Vue interface, you must recompile the frontend bundle:
```bash
npm install
npm run build
```

### 4. Database Schema and Seeding
Run any new database migrations and seed the updated **Spatie Roles & Permissions Matrix**:
```bash
# Run migrations safely
php artisan migrate --force

# Seed the Spatie permissions and role assignments
php artisan db:seed --class=RoleSeeder --force
```
> [!NOTE]
> Running the `RoleSeeder` is 100% safe. It uses `firstOrCreate()` to add new capabilities to the database without modifying or deleting existing users or their currently assigned roles.

### 5. Reset and Clear Caches
Spatie caches permissions to maximize database performance. You must clear and rebuild the application caches so the changes take effect instantly:
```bash
# Reset Spatie permission cache
php artisan permission:cache-reset

# Clear and rebuild standard Laravel caches
php artisan optimize:clear
php artisan optimize
```

### 6. Restart Background Workers
Restart Supervisor queue processes to ensure the active daemon workers execute the upgraded PHP code:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all
```

---

## Verification on Production

1. Log in as an Administrator (`admin@mirco.com`).
2. Navigate to the **Security Role Permission Matrix** (`/admin/users` -> Security Role Permission Matrix tab).
3. Toggle checkmarks to adjust roles (e.g., enable or disable permissions for Technicians).
4. Save the adjustments and verify that the corresponding pages and action buttons automatically adapt in real-time.
