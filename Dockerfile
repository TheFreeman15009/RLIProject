ARG PHP_VERSION
FROM php:$PHP_VERSION-fpm
RUN apt-get update -y && apt-get install -y git curl unzip libxslt-dev \
        libzip-dev libpng-dev libgmp-dev zlib1g-dev libffi-dev

RUN docker-php-ext-install pdo_mysql bcmath xsl
RUN docker-php-ext-install calendar exif ffi gd
RUN docker-php-ext-install gettext gmp pcntl
RUN docker-php-ext-install shmop sockets sysvmsg sysvsem sysvshm zip

ARG PROJECT_DIR
WORKDIR $PROJECT_DIR

RUN sed -i "s/user = www-data/user = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = root/g" /usr/local/etc/php-fpm.d/www.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]