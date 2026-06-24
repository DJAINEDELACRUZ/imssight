FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN { \
    echo 'upload_max_filesize=8M'; \
    echo 'post_max_size=10M'; \
} > /usr/local/etc/php/conf.d/imssight-uploads.ini

RUN a2enmod rewrite
