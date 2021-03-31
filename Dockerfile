FROM php:7
RUN apt-get update -y && apt-get install -y openssl zip unzip git \
    libpng-dev \
    libonig-dev \
    libxml2-dev
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_mysql mbstring
WORKDIR /app
COPY . /app
RUN composer update --with-all-dependencies

CMD php artisan serve --host=0.0.0.0 --port=8181
EXPOSE 8181
