#ISTSI

Rename /app/src/settings.php.dist to /app/src/settings.php and fill in the blanks

After docker-compose build:
- docker-compose exec php bash
- chown -R www-data:www-data /var/www/cache /var/www/data /var/www/logs
