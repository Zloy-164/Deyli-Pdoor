FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

# Force Apache to use exactly one MPM: prefork.
RUN rm -f \
      /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf \
    && ln -sf ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -sf ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite headers \
    && apache2ctl configtest

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chmod +x /var/www/html/docker-entrypoint.sh \
    && mkdir -p /data/uploads \
    && chown -R www-data:www-data /data /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV UPLOAD_DIR=/data/uploads

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
