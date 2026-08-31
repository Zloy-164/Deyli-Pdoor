FROM php:8.3-cli

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chmod +x /var/www/html/docker-entrypoint.sh \
    && mkdir -p /data/uploads

ENV UPLOAD_DIR=/data/uploads
ENV PORT=80

EXPOSE 80

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
