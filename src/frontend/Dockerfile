FROM php:8.2-apache

WORKDIR /var/www

RUN rm /bin/sh && ln -s /bin/bash /bin/sh

# Actualiza paquetes del sistema para aplicar parches de seguridad
RUN apt-get update && apt-get upgrade -y && apt-get dist-upgrade -y && apt-get autoremove -y

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

# Copia la configuración personalizada de Apache
COPY .deploy/config/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

#install mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql gd zip exif
RUN docker-php-ext-enable mysqli pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer \
| php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite

# Copia el código de la aplicación
COPY ./src /var/www
#COPY .deploy/config/env /var/www/.env

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

RUN rm -rf vendor
RUN rm -rf node_modules
RUN rm -rf composer.lock

# Instala dependencias de Composer si existe composer.json
RUN  COMPOSER_ALLOW_SUPERUSER=1 composer update  --no-dev

# Instala Node.js y NVM
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.5/install.sh | bash\
    && . ~/.nvm/nvm.sh\
    && nvm --version\
    && nvm install 20\
    && nvm use 20

#instala npm
#RUN npm install