# Fix for images not accessible after deployment

# 1. Create symbolic link for storage (run inside container)
docker-compose exec app php artisan storage:link

# 2. Copy images to storage directory
docker-compose cp ./data app:/var/www/html/data
docker-compose exec app bash -c "
    mkdir -p storage/app/cars &&
    cp -r data/* storage/app/cars/ 2>/dev/null || true &&
    chown -R appuser:www-data storage/app/cars &&
    chmod -R 755 storage/app/cars
"

# 3. Verify images are accessible
docker-compose exec app ls -la storage/app/cars/ | head -20

# 4. Check Nginx configuration for storage location
# Make sure default.conf has:
# location /storage {
#     alias /var/www/html/storage/app/public;
#     try_files $uri =404;
# }

# 5. Clear cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# 6. Test image access
# Open browser: http://localhost:8081/storage/cars/xxxxx.jpg
