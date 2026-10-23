FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
COPY src/ ./src/
# --ignore-platform-reqs: this stage only resolves packages and dumps the
# autoloader, and the composer image has no pdo_mysql. The runtime below does.
RUN composer install --no-dev --no-interaction --no-progress \
      --optimize-autoloader --ignore-platform-reqs

FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql && a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

# Routes anything that is not a real file to the front controller. The route
# table keeps the original URLs, which end in .php and do not exist as files.
#
# This has to be mod_rewrite rather than FallbackResource: for a path ending
# in .php the PHP handler claims the request and then fails to stat the
# script, so the fallback never runs. Rewriting happens early enough.
RUN printf '%s\n' \
      '<Directory /var/www/html/public>' \
      '    RewriteEngine On' \
      '    RewriteCond %{REQUEST_FILENAME} !-f' \
      '    RewriteCond %{REQUEST_FILENAME} !-d' \
      '    RewriteRule ^ /index.php [L]' \
      '</Directory>' \
      > /etc/apache2/conf-available/buffalo.conf \
    && a2enconf buffalo

COPY --from=deps /app/vendor /var/www/html/vendor
COPY src/       /var/www/html/src/
COPY templates/ /var/www/html/templates/
COPY public/    /var/www/html/public/

# The administrator writes product photos and data sheets into these.
RUN chown -R www-data:www-data \
      /var/www/html/public/images /var/www/html/public/ficha_tecnica
