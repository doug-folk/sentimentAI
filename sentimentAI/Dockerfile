FROM php:8.2-fpm

# 1. Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    netcat-traditional \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Cria estrutura de diretórios com permissões corretas
RUN mkdir -p /var/www/storage/framework/{views,cache,sessions} \
    && chown -R www-data:www-data /var/www/storage \
    && chmod -R 775 /var/www/storage

WORKDIR /var/www

# 4. Copia e instala dependências (otimização de cache de camadas)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# 5. Copia o restante da aplicação
COPY . .

# 6. Finaliza instalação do Composer e ajusta permissões
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/bootstrap/cache

# 7. Configura entrypoint
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]