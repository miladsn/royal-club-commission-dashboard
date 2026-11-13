#!/bin/sh
set -e


if [ -d "/var/www/storage" ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache
fi


chmod 1777 /tmp

exec "$@"
