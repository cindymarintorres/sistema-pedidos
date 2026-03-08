#!/bin/bash
set -e

# Copiar .env.example a .env si no existe todavía
if [ ! -f /var/www/html/.env ]; then
    echo "Archivo .env no encontrado. Copiando desde .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generar APP_KEY si está vacía
if grep -q "^APP_KEY=$" /var/www/html/.env; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Crear directorios de storage que Laravel necesita y corregir permisos
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Limpiar y regenerar caches de configuración
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Iniciar Apache
exec apache2-foreground