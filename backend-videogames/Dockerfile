FROM php:8.0-apache

# --- CONFIGURACIÓN DEL SERVIDOR APACHE ---
# 1. Activar el módulo 'rewrite' para las reglas de URL amigables.
RUN a2enmod rewrite
# 2. Activar el módulo 'headers' para poder añadir cabeceras (como CORS) desde .htaccess.
RUN a2enmod headers
# 3. Permitir que Apache use los archivos .htaccess para sobreescribir la configuración.
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# --- INSTALACIÓN DE DEPENDENCIAS ---
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring gd
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- PASOS DE LA APLICACIÓN ---
WORKDIR /var/www/html
COPY src/ .
RUN composer install --no-interaction --no-plugins --no-scripts

# Exponer el puerto
EXPOSE 80
