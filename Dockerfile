ARG DOCKER_FROM_IMAGE=php:8.3-fpm

#
# Build Composer Base Image
#
FROM composer:2.2 AS composer

#
# Build Server Deployment Image
#
# trunk-ignore(hadolint/DL3006)
FROM ${DOCKER_FROM_IMAGE}

ARG APP_KEY_ARG

USER www-data

# Local proxy config (remove for server deployment)
# ENV http_proxy=http://198.161.14.25:8080

ENV ETC_DIR=/usr/local/etc
ENV PHP_INI_DIR $ETC_DIR/php
ENV PHP_CONF_DIR=${PHP_INI_DIR}/conf.d
ENV PHP_INI_FILE ${PHP_CONF_DIR}/php.ini
ENV BUILD_DIR=/tmp/build
ENV WWW_DIR=/var/www
# ENV APP_DIR=${BUILD_DIR}/html
ENV STORAGE_DIR=/var/www/storage
ENV APP_STORAGE_DIR=${STORAGE_DIR}/app
ENV PUBLIC_STORAGE_DIR=${APP_STORAGE_DIR}/public

# Switch to primary user for OS updates / installations
USER root

# Clean up existing sources and use a different Debian mirror
RUN rm -f /etc/apt/sources.list.d/* ; \
    echo "deb http://deb.debian.org/debian bookworm main" > /etc/apt/sources.list; \
    echo "deb http://ftp.us.debian.org/debian bookworm main" >> /etc/apt/sources.list; \
		echo "deb http://ftp.ca.debian.org/debian/ bookworm main" >> /etc/apt/sources.list

RUN apt-get update -y || { sleep 30; apt-get update -y; } || { sleep 60; apt-get update -y; } || { sleep 90; apt-get update -y; } || { sleep 120; apt-get update -y; } \
	&& apt-get upgrade -y --fix-missing

RUN set -eux; \
  dpkg --configure -a; \
  apt-get -f install; \
  apt-get install -y --no-install-recommends \
	dos2unix \
	openssh-client \
	zlib1g-dev \
	libicu-dev \
	g++ \
	rsync \
	wget \
	grsync \
	tar \
  git \
  zip \
  unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Install PHPRedis
RUN rm -rf /phpredis
RUN git clone https://github.com/phpredis/phpredis.git /phpredis
WORKDIR /phpredis
RUN	phpize && ./configure && make && make install

# Copy Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy application files
WORKDIR ${BUILD_DIR}
COPY . ${BUILD_DIR}
COPY example.env ${BUILD_DIR}/.env
RUN chmod 644 ${BUILD_DIR}/.env

# COPY the PHP extension installer with curl using root permissions
RUN curl -L https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
			apcu \
			gd \
			xmlrpc \
			pdo_mysql \
			mysqli \
			soap \
			intl \
			zip \
			xsl \
			opcache \
			ldap \
			exif

# Ensure the www-data user has the necessary permissions
RUN chown -R www-data:www-data ${BUILD_DIR}

# Install dependencies
RUN --mount=type=cache,target=/tmp/cache composer install --working-dir=${BUILD_DIR} --no-scripts --no-autoloader --prefer-dist --no-dev && \
	composer update  && \
	chgrp -R 0 ${BUILD_DIR} && \
	chmod -R g=u ${BUILD_DIR}

USER www-data

# Check if composer.json exists and output its contents
RUN if [ -f "composer.json" ]; then \
		echo "composer.json found in ${BUILD_DIR}"; \
		cat composer.json; \
else \
		echo "composer.json not found in ${BUILD_DIR}"; \
		exit 1; \
fi

USER root

# Create the "public" folder in /storage/app
WORKDIR /

RUN mkdir -p ${STORAGE_DIR} \
	&& mkdir -p ${APP_STORAGE_DIR} \
	&& mkdir -p ${PUBLIC_STORAGE_DIR}

# Set appropriate permissions for the /storage directories
RUN chown -R www-data:www-data ${PUBLIC_STORAGE_DIR} && \
	chown -R www-data:www-data  ${APP_STORAGE_DIR}

# Copy the contents from local ./storage/app/public directory to the target directory
COPY ./storage ${STORAGE_DIR}

# Create cache and session storage structure
RUN chmod -R 755 ${STORAGE_DIR} && \
		chown -R www-data:www-data ${APP_STORAGE_DIR} ${STORAGE_DIR}/framework ${STORAGE_DIR}/logs

# Copy Server Config files (Apache / PHP)
# RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_FILE}
COPY ./openshift/config/php/php.ini-production ${PHP_INI_FILE}
COPY ./openshift/config/php/php.ini ${PHP_CONF_DIR}/app-php.ini
COPY ./openshift/config/php/www.conf ${ETC_DIR}/php-fpm.d/www.conf
COPY --chown=www-data:www-data ./openshift/config/php/opcache.ini ${PHP_CONF_DIR}/opcache.ini
COPY ./openshift/config/php/info.php ${BUILD_DIR}/public/info/info.php

# Add Healthcheck
RUN wget --progress=dot:giga -O /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
  && chmod +x /usr/local/bin/php-fpm-healthcheck \
  && wget -O $(which php-fpm-healthcheck) \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
  && chmod +x $(which php-fpm-healthcheck)

# Add cron script
COPY ./openshift/config/cron/cron.sh /usr/local/bin/cron.sh
# Convert line endings of the script to ensure compatibility
RUN chmod +x /usr/local/bin/cron.sh && \
	dos2unix /usr/local/bin/cron.sh

# Add migration script
COPY ./openshift/scripts/migrate-build-files.sh /usr/local/bin/migrate-build-files.sh
# Convert line endings of the script to ensure compatibility
RUN chmod +x /usr/local/bin/migrate-build-files.sh && \
	dos2unix /usr/local/bin/migrate-build-files.sh

# Add migration test script
COPY ./openshift/scripts/test-migration-complete.sh /usr/local/bin/test-migration-complete.sh
# Convert line endings of the script to ensure compatibility
RUN chmod +x /usr/local/bin/test-migration-complete.sh && \
	dos2unix /usr/local/bin/test-migration-complete.sh

USER www-data

WORKDIR ${BUILD_DIR}

# Debugging output
RUN echo "APP_KEY_ARG: $APP_KEY_ARG"
RUN echo "APP_KEY_ARG length: ${#APP_KEY_ARG}"
RUN echo "APP_KEY from .env: $(grep APP_KEY .env)"

# Check if APP_KEY_ARG or APP_KEY is set and valid
RUN if [ -z "$APP_KEY_ARG" ] || ! echo "$APP_KEY_ARG" | grep -Eq '^base64:[A-Za-z0-9+/=]{43}$'; then \
        echo "APP_KEY_ARG is not set or invalid ($APP_KEY_ARG). Checking APP_KEY..."; \
        if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -Eq '^base64:[A-Za-z0-9+/=]{43}$'; then \
            echo "APP_KEY is not set or invalid ($APP_KEY). Generating a new APP_KEY..."; \
            php artisan key:generate --ansi; \
        else \
            echo "APP_KEY is valid in env. Using existing APP_KEY."; \
            echo "APP_KEY=$APP_KEY" >> .env; \
        fi; \
    else \
        echo "APP_KEY_ARG is valid. Using APP_KEY_ARG as APP_KEY."; \
        echo "APP_KEY=$APP_KEY_ARG" >> .env; \
    fi

# This won't work here, as we're migrating files to www after deployment
# WORKDIR /var/www
# RUN php artisan storage:link
