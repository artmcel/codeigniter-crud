FROM php:8.1-apache

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mysqli pdo pdo_mysql intl

# Habilita mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copia la configuración personalizada de Apache
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Copia el código de la aplicación
COPY . /var/www/html

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Instala Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Instala dependencias de Composer si existe composer.json
RUN if [ -f /var/www/html/composer.json ]; then composer update --no-interaction --prefer-dist --optimize-autoloader; fi

EXPOSE 80