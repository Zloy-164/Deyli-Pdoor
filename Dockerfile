FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true

RUN a2enmod mpm_prefork rewrite headers

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chmod +x /var/www/html/docker-entrypoint.sh \
    && mkdir -p /data/uploads \
    && chown -R www-data:www-data /data /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV UPLOAD_DIR=/data/uploads

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
