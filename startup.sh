#!/bin/bash

# --- 1. PERMISSIONS ---
cd /home/site/wwwroot
echo "Fixing permissions..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# --- 2. CONFIGURE NGINX ---
echo "Configuring Nginx for Laravel..."
# We write the Nginx config directly to the default file.
# IMPORTANT: We use \$ to escape variables so Bash doesn't try to read them.
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot/public;
    index  index.php index.html index.htm;
    server_name  example.com www.example.com; 

    location / {
        # This is CRITICAL for Laravel to work
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Redirect server error pages
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /html/;
    }

    # Pass PHP scripts to FastCGI server
    location ~ [^/]\.php(/|$) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param HTTP_PROXY "";
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_param QUERY_STRING \$query_string;
        fastcgi_intercept_errors on;
        fastcgi_connect_timeout         300; 
        fastcgi_send_timeout           3600; 
        fastcgi_read_timeout           3600;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    # Deny access to .git, .env, etc.
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF


# --- 3. FILE RESTORATION ---
echo "Restoring persistent files..."
cp -f /home/site/.env /home/site/wwwroot/.env
#cp -f /home/site/firebase-credentials.json /home/site/wwwroot/firebase-credentials.json
mkdir /home/site/wwwroot/storage/certs
cp -f /home/site/DigiCertGlobalRootG2.crt.pem /home/site/wwwroot/storage/certs/azure-ca.pem

# --- 4. LARAVEL OPTIMIZATION ---
echo "Running Laravel setup..."
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 5. START SERVICES ---
echo "Starting services..."
# We must reload Nginx to pick up the new config
service nginx reload

# Start PHP-FPM in the background, then keep the container alive
# Note: Azure's default Docker command usually starts php-fpm, 
# so we just need to ensure Nginx runs.