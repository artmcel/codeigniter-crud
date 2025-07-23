FROM php:7.4-cli

RUN rm /bin/sh && ln -s /bin/bash /bin/sh

RUN apt-get update 
RUN apt-get install -y git curl zip zlib1g zlib1g-dev libzip-dev unzip

COPY ./src /usr/local/migration

RUN docker-php-ext-install mysqli pdo pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

RUN chmod +x /usr/local/migration/run.php
RUN ln -s /usr/local/migration/run.php /usr/local/bin/migrate

WORKDIR /usr/local/migration

RUN composer install

