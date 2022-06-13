#
#
# Build Composer Base Image
#
#
FROM composer as composer

# Local proxy config (remove for server deployment)
ENV http_proxy=http://198.161.14.25:8080
ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /app
COPY . /app

RUN composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --prefer-dist
RUN composer require kalnoy/nestedset --ignore-platform-reqs
RUN composer require doctrine/dbal --ignore-platform-reqs
RUN composer require awobaz/compoships --ignore-platform-reqs
RUN chgrp -R 0 /app && \
    chmod -R g=u /app

#
#
# Build Server Deployment Image
#
#
FROM php:8.0-apache

# Local proxy config (remove for server deployment)
ENV http_proxy=http://198.161.14.25:8080

RUN apt-get update -y && apt -y upgrade && apt-get install -y \
    openssl

RUN apt-get install -y \
    ssh-client \
    zip \
    unzip

# Enable "mod_rewrite" – http://httpd.apache.org/docs/current/mod/mod_rewrite.html
RUN a2enmod rewrite
# Enable "mod_headers" – http://httpd.apache.org/docs/current/mod/mod_headers.html
RUN a2enmod headers
# Enable "mod_expires" – http://httpd.apache.org/docs/current/mod/mod_expires.html
RUN a2enmod expires

#INSTALL APCU
#RUN pecl install apcu-${APCU_VERSION} && docker-php-ext-enable apcu
#RUN echo "extension=apcu.so" > /usr/local/etc/php/php.ini
#RUN echo "apc.enable_cli=1" > /usr/local/etc/php/php.ini
#RUN echo "apc.enable=1" > /usr/local/etc/php/php.ini
#APCU
#RUN php --ini
#RUN php --info | grep apc

RUN echo '\
  opcache.interned_strings_buffer=16\n\
  opcache.load_comments=Off\n\
  opcache.max_accelerated_files=16000\n\
  opcache.save_comments=Off\n\
  ' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

RUN echo "deb https://packages.sury.org/php/ buster main" | tee /etc/apt/sources.list.d/php.list
RUN docker-php-ext-install pdo pdo_mysql opcache

USER root

COPY --chown=www-data:www-data --from=composer /app /var/www/html

# Copy Server Config files (Apache / PHP)
COPY --chown=www-data:www-data config/server_files/apache2.conf /etc/apache2/apache2.conf
COPY --chown=www-data:www-data config/server_files/ports.conf /etc/apache2/ports.conf
COPY --chown=www-data:www-data config/server_files/.htaccess /var/www/html/public/.htaccess
COPY --chown=www-data:www-data config/server_files/php.ini /usr/local/etc/php/php.ini
COPY --chown=www-data:www-data config/server_files/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# For Debug Purposes - Comment out for production
# COPY --chown=www-data:www-data config/server_files/info.php /var/www/html/public/info.php

EXPOSE 8000

ENTRYPOINT ["apachectl", "-D", "FOREGROUND"]
