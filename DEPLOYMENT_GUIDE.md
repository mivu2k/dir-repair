# MEI: Final Deployment Guide (LXC & MySQL Production)

This guide provides a comprehensive roadmap for deploying the **MEI Technical Audit & Reporting System** in a production Linux Container (LXC) using **MySQL**.

## 1. System Requirements & Stack
- **OS**: Ubuntu 22.04+ (Recommended)
- **PHP**: 8.4 (with mysql, fpm, gd, zip, bcmath, intl)
- **Database**: MySQL 8.0+
- **Web Server**: Nginx

## 2. Server Provisioning

Run these commands inside your LXC to prepare the environment:

```bash
# Core System Update
sudo apt update && sudo apt upgrade -y

# Add PHP 8.4 Repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install Essential Stack
sudo apt install -y nginx php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml php8.4-curl \
    php8.4-mysql php8.4-gd php8.4-zip php8.4-bcmath php8.4-intl nodejs npm mysql-server supervisor
```

## 3. Database Configuration (MySQL)

Configure your production database with your specific credentials:

```bash
# Secure MySQL installation (follow prompts)
sudo mysql_secure_installation

# Create Database and User
sudo mysql -u root -p
```

**Inside the MySQL shell:**
```sql
CREATE DATABASE mei_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mei_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON mei_production.* TO 'mei_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Application Setup

Clone the repository and initialize the project components:

```bash
cd /var/www
git clone <your-repo-url> vb-system
cd vb-system

# Set Production Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Install Dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

## 5. Environment Configuration

```bash
cp .env.example .env
nano .env
```

**Mandatory MySQL Adjustments:**
```env
APP_NAME="MEI TECHNICAL"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mei_production
DB_USERNAME=mei_user
DB_PASSWORD=YOUR_SECURE_PASSWORD

QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

**Initialize Application:**
```bash
# Security & Migrations
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Initialization (Seeders if needed)
# php artisan db:seed --force
```

## 6. Nginx & FPM Configuration

`sudo nano /etc/nginx/sites-available/mei-system`

```nginx
server {
    listen 80;
    server_name your-app-domain.com;
    root /var/www/vb-system/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;

    # Performance logging
    access_log off;
    error_log  /var/log/nginx/mei.error.log error;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activate Site:**
```bash
sudo ln -s /etc/nginx/sites-available/mei-system /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx
```

## 7. Background Processes (Supervisor)

Configure Supervisor to handle the queue for report generation:
`sudo nano /etc/supervisor/conf.d/mei-worker.conf`

```ini
[program:mei-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/vb-system/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/vb-system/storage/logs/worker.log
```

## 8. Final Optimization

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> [!TIP]
> **Scheduler**: Ensure Laravel's background tasks run correctly.
> Add this line to `crontab -e`:
> `* * * * * cd /var/www/vb-system && php artisan schedule:run >> /dev/null 2>&1`
