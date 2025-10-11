# 🚀 Deployment & Production

## Overview

This guide covers deploying Hunter to production with PostgreSQL, including server setup, environment configuration, performance optimization, and monitoring.

## Prerequisites

### System Requirements

- **Server**: VPS or dedicated server (minimum 2GB RAM, 2 CPU cores)
- **OS**: Ubuntu 22.04+ or similar Linux distribution
- **PHP**: 8.4+
- **Database**: PostgreSQL 14+
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Redis**: For cache, sessions, and queues
- **Node.js**: 18+ for building assets

## Production Stack

```
┌─────────────────────────────────────────────────┐
│                   Load Balancer                  │
│                   (Optional)                     │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│                 Nginx (Web Server)               │
│            SSL/TLS Termination                   │
└──────────────────┬──────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────┐
│          Laravel Application (PHP-FPM)           │
│         + Laravel Reverb (WebSockets)            │
└────┬─────────────────────────────────────┬──────┘
     │                                     │
┌────▼────────┐                    ┌──────▼───────┐
│ PostgreSQL  │                    │    Redis     │
│  (Database) │                    │ (Cache/Queue)│
└─────────────┘                    └──────────────┘
```

## 1. Server Setup

### Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### Install PHP 8.4

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.4-fpm php8.4-cli php8.4-common \
    php8.4-pgsql php8.4-redis php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-bcmath
```

### Install PostgreSQL

```bash
# Install PostgreSQL 16
sudo apt install -y postgresql-16 postgresql-contrib

# Start and enable
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Create database and user
sudo -u postgres psql

CREATE DATABASE devhunter;
CREATE USER devhunter WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE devhunter TO devhunter;
ALTER DATABASE devhunter OWNER TO devhunter;
\q
```

### Install Redis

```bash
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
# Set: maxmemory 256mb
# Set: maxmemory-policy allkeys-lru

sudo systemctl restart redis
sudo systemctl enable redis
```

### Install Nginx

```bash
sudo apt install -y nginx

sudo systemctl start nginx
sudo systemctl enable nginx
```

### Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Install Supervisor

```bash
sudo apt install -y supervisor

sudo systemctl start supervisor
sudo systemctl enable supervisor
```

## 2. Application Deployment

### Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/akira-io/devhunter.git
sudo chown -R www-data:www-data devhunter
cd devhunter
```

### Install Dependencies

```bash
# PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Node dependencies and build assets
npm ci
npm run build
```

### Environment Configuration

```bash
# Copy and configure .env
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```

#### Production `.env` Configuration

```env
APP_NAME="Hunter"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devhunter.com

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=devhunter
DB_USERNAME=devhunter
DB_PASSWORD=your_secure_password

# Cache & Sessions (Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@devhunter.com"
MAIL_FROM_NAME="${APP_NAME}"

# Laravel Reverb (WebSockets)
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST="reverb.devhunter.com"
REVERB_PORT=443
REVERB_SCHEME=https

# Meilisearch
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key

# OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_secret
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Storage (DigitalOcean Spaces or AWS S3)
FILESYSTEM_DISK=digitalocean

DO_SPACES_KEY=your_spaces_key
DO_SPACES_SECRET=your_spaces_secret
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=devhunter

# Optional: Sentry for error tracking
SENTRY_LARAVEL_DSN=your_sentry_dsn
```

### Generate Application Key

```bash
sudo -u www-data php artisan key:generate
```

### Run Migrations

```bash
sudo -u www-data php artisan migrate --force
```

### Optimize Application

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
```

### Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/devhunter
sudo chmod -R 755 /var/www/devhunter
sudo chmod -R 775 /var/www/devhunter/storage
sudo chmod -R 775 /var/www/devhunter/bootstrap/cache
```

## 3. Nginx Configuration

### Create Site Configuration

```bash
sudo nano /etc/nginx/sites-available/devhunter
```

```nginx
# HTTP → HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name devhunter.com www.devhunter.com;
    return 301 https://$server_name$request_uri;
}

# Main HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name devhunter.com www.devhunter.com;
    root /var/www/devhunter/public;
    
    index index.php;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/devhunter.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/devhunter.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_status 429;
    
    # PHP handling
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Increase timeouts for long requests
        fastcgi_read_timeout 300;
    }
    
    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Static assets caching
    location ~* \.(jpg|jpeg|gif|png|webp|svg|css|js|ico|xml|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}

# WebSocket server (Laravel Reverb)
upstream reverb {
    server 127.0.0.1:8080;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name reverb.devhunter.com;
    
    ssl_certificate /etc/letsencrypt/live/devhunter.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/devhunter.com/privkey.pem;
    
    location / {
        proxy_pass http://reverb;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
}
```

### Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/devhunter /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 4. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d devhunter.com -d www.devhunter.com -d reverb.devhunter.com

# Auto-renewal is set up automatically
# Test renewal
sudo certbot renew --dry-run
```

## 5. Supervisor Configuration

### Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/devhunter-queue.conf
```

```ini
[program:devhunter-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/devhunter/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/devhunter/storage/logs/queue-worker.log
stopwaitsecs=3600
```

### Laravel Reverb (WebSockets)

```bash
sudo nano /etc/supervisor/conf.d/devhunter-reverb.conf
```

```ini
[program:devhunter-reverb]
process_name=%(program_name)s
command=php /var/www/devhunter/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/devhunter/storage/logs/reverb.log
```

### Laravel Scheduler

```bash
sudo nano /etc/supervisor/conf.d/devhunter-scheduler.conf
```

```ini
[program:devhunter-scheduler]
process_name=%(program_name)s
command=/bin/bash -c "while true; do php /var/www/devhunter/artisan schedule:run --verbose --no-interaction & sleep 60; done"
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/devhunter/storage/logs/scheduler.log
```

### Reload Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```

## 6. Performance Optimization

### PHP-FPM Tuning

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Increase memory limit
php_admin_value[memory_limit] = 256M
```

```bash
sudo systemctl restart php8.4-fpm
```

### PostgreSQL Tuning

```bash
sudo nano /etc/postgresql/16/main/postgresql.conf
```

```ini
# Memory settings (for 4GB RAM server)
shared_buffers = 1GB
effective_cache_size = 3GB
maintenance_work_mem = 256MB
work_mem = 16MB

# Connection settings
max_connections = 100

# Query planning
random_page_cost = 1.1  # For SSD
effective_io_concurrency = 200

# Write ahead log
wal_buffers = 16MB
checkpoint_completion_target = 0.9
```

```bash
sudo systemctl restart postgresql
```

### Redis Configuration

```bash
sudo nano /etc/redis/redis.conf
```

```ini
maxmemory 512mb
maxmemory-policy allkeys-lru
save ""
```

```bash
sudo systemctl restart redis
```

## 7. Monitoring & Logging

### Install Meilisearch (Search Engine)

```bash
curl -L https://install.meilisearch.com | sh
sudo mv meilisearch /usr/local/bin/

# Create systemd service
sudo nano /etc/systemd/system/meilisearch.service
```

```ini
[Unit]
Description=Meilisearch
After=network.target

[Service]
Type=simple
User=www-data
ExecStart=/usr/local/bin/meilisearch --master-key your_master_key --http-addr 127.0.0.1:7700
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl start meilisearch
sudo systemctl enable meilisearch
```

### Laravel Logs

```bash
# View logs
tail -f /var/www/devhunter/storage/logs/laravel.log

# Rotate logs
sudo nano /etc/logrotate.d/devhunter
```

```
/var/www/devhunter/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart devhunter-queue:*
    endscript
}
```

### Monitoring with Sentry

Install Sentry SDK (already in `composer.json`):

```php
// config/sentry.php is already configured
// Set SENTRY_LARAVEL_DSN in .env
```

## 8. Backup Strategy

### Database Backups

```bash
sudo nano /usr/local/bin/backup-devhunter.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/devhunter"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="devhunter"

mkdir -p $BACKUP_DIR

# PostgreSQL backup
pg_dump -U devhunter $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-devhunter.sh

# Add to crontab
sudo crontab -e
```

```cron
0 2 * * * /usr/local/bin/backup-devhunter.sh
```

### Application Backups

Consider using S3/Spaces for:
- User uploads
- Database backups
- Application state

## 9. Deployment Script

```bash
# /var/www/devhunter/deploy.sh
#!/bin/bash

set -e

echo "🚀 Starting deployment..."

# Enter maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo supervisorctl restart devhunter-queue:*
sudo supervisorctl restart devhunter-reverb

# Exit maintenance mode
php artisan up

echo "✅ Deployment completed!"
```

## 10. Security Checklist

- [ ] `.env` file is not accessible via web
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] SSL/TLS enabled (HTTPS)
- [ ] Firewall configured (ufw)
- [ ] Regular backups scheduled
- [ ] Rate limiting configured
- [ ] Security headers set
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (Eloquent)
- [ ] XSS protection (Blade escaping)
- [ ] File upload validation
- [ ] Sentry error tracking enabled

## 11. Troubleshooting

### Application Errors

```bash
# Check Laravel logs
tail -f /var/www/devhunter/storage/logs/laravel.log

# Check PHP-FPM logs
tail -f /var/log/php8.4-fpm.log

# Check Nginx errors
tail -f /var/log/nginx/error.log
```

### Queue Not Processing

```bash
# Check supervisor status
sudo supervisorctl status devhunter-queue:*

# Restart queue workers
sudo supervisorctl restart devhunter-queue:*
```

### WebSocket Connection Issues

```bash
# Check Reverb status
sudo supervisorctl status devhunter-reverb

# Check Reverb logs
tail -f /var/www/devhunter/storage/logs/reverb.log
```

### Database Connection Issues

```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Test connection
psql -U devhunter -d devhunter -h 127.0.0.1
```

## Related Documentation

- [Getting Started](./01-getting-started.md) - Local development setup
- [Testing](./10-testing.md) - Testing before deployment
- [API Reference](./08-api-reference.md) - API documentation
