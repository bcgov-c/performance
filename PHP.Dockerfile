ARG DOCKER_FROM_IMAGE=php:8.4.0alpha4-fpm-bullseye
ARG PHP_INI_ENVIRONMENT=production

#
# Build Composer Base Image
#
FROM composer AS composer

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=2000

#
# Build PHP / Laravel Server Deployment Image
#
FROM ${DOCKER_FROM_IMAGE}

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENV ETC_DIR=/usr/local/etc
ENV PHP_INI_DIR $ETC_DIR/php
ENV PHP_INI_FILE $PHP_INI_DIR/conf.d/php.ini
ENV APP_DIR=/var/www/html
ENV STORAGE_DIR=/var/www/storage
ENV STORAGE_APP_DIR=${STORAGE_DIR}/app
ENV STORAGE_PUBCLIC_DIR=${STORAGE_APP_DIR}/public

WORKDIR ${STORAGE_APP_DIR}
COPY . ${STORAGE_APP_DIR}
WORKDIR /

RUN echo "Building PHP version: ${DOCKER_FROM_IMAGE} for ${PHP_INI_ENVIRONMENT} environment"

# Create the "public" folder in /storage/app
RUN mkdir -p ${STORAGE_PUBCLIC_DIR}

# COPY --chown=www-data:www-data --from=composer /app ${APP_DIR}
# COPY --chown=www-data:www-data --from=composer /vendor /vendor

# Set appropriate permissions for the /storage/app/public directory
RUN chown -R www-data:www-data ${STORAGE_PUBCLIC_DIR}
# Copy the contents from your local ./storage/app/public directory to the target directory
COPY ./storage/app/public ${STORAGE_PUBCLIC_DIR}
# Set permissions for the copied files and directories
RUN chmod -R 755 ${STORAGE_PUBCLIC_DIR}

# Update and install additional tools
RUN apt-get update && apt-get upgrade -y --fix-missing && apt-get install --no-install-recommends -y \
    zlib1g-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libxslt-dev \
    libldap-dev \
    libfreetype-dev \
    wget \
    libfcgi-bin \
    libonig-dev \
    rsync \
    openssl \
    ssh-client \
    zip \
    unzip \
    vim

RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    apcu \
    gd \
    # xdebug \
    xmlrpc \
    pdo \
    pdo_mysql \
    mysqli \
    soap \
    intl \
    zip \
    xsl \
    opcache \
    ldap \
    exif \
    mbstring

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    apcu \
    gd \
    # xdebug \
    xmlrpc \
    pdo \
    pdo_mysql \
    mysqli \
    soap \
    intl \
    zip \
    xsl \
    opcache \
    ldap \
    exif \
    mbstring

RUN pecl install -o -f redis \
  #  xdebug \
  && docker-php-ext-enable redis \
  xmlrpc  \
  # xdebug \
  && rm -rf /tmp/pear

RUN apt-get update -y && \
	apt-get upgrade -y --fix-missing && \
	# apt-get dist-upgrade -y && \
	dpkg --configure -a && \
	apt-get -f install && \
	apt-get install -y zlib1g-dev libicu-dev g++ && \
	apt-get install rsync grsync && \
	apt-get install tar && \
	set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	docker-php-ext-install -j "$(nproc)" \
	; \
	\
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false;

RUN wget --progress=dot:giga -O /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
  && chmod +x /usr/local/bin/php-fpm-healthcheck \
  && wget -O $(which php-fpm-healthcheck) \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
  && chmod +x $(which php-fpm-healthcheck)

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_FILE"
COPY ./openshift/config/php/php.ini "$PHP_INI_DIR/conf.d/app-php.ini"
COPY ./openshift/config/php/www.conf "$ETC_DIR/php-fpm.d/www.conf"

# Add commands for site upgrades / migrations
COPY ./openshift/scripts/migrate-build-files.sh /usr/local/bin/migrate-build-files.sh
COPY ./openshift/scripts/test-migration-complete.sh /usr/local/bin/test-migration-complete.sh

# Set the working directory
WORKDIR ${APP_DIR}

# Run php artisan storage:link to create the symbolic link
# RUN php artisan storage:link
