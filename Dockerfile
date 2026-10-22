# PHP 5.6 is the last version that ships ext/mysql (mysql_connect et al.),
# which this codebase still uses throughout. Moving off it is the point of
# the resurrection work -- until then, this is the only runtime that works.
FROM php:5.6-apache

RUN docker-php-ext-install mysql

COPY . /var/www/html/

# The admin uploads product photos and PDFs into these directories at runtime.
RUN chown -R www-data:www-data /var/www/html/images /var/www/html/ficha_tecnica
