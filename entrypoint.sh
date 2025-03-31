#!/bin/bash
set -e

echo "🔄 Waiting for PostgreSQL..."
while ! nc -z db 5432; do
  sleep 1
done

echo "✅ Database ready! Initializing Laravel..."

# 1. Configuração inicial
echo "⚙️ Creating or updating .env file..."
cp -f .env.example .env

echo "🔑 Generating application key..."
php artisan key:generate --force

# Garante permissões corretas
chmod 777 .env


# 2. Dependências (executa apenas se necessário)
if [ ! -d "vendor" ]; then
  echo "📦 Installing dependencies..."
  composer install --no-interaction --optimize-autoloader
fi

# 3. Garante permissões corretas
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 4. Configuração do banco de dados
echo "🛠️ Running migrations..."
php artisan migrate --force

# 5. Configuração de cache (usando file/database)
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 6. Otimização (após tudo estar configurado)
echo "⚡ Optimizing application..."
php artisan optimize
php artisan view:cache

# 7. Inicia o PHP-FPM (mantém em primeiro plano)
echo "🚀 Starting PHP-FPM..."
exec php-fpm
