FROM php:8.3-cli

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /data/uploads

ENV UPLOAD_DIR=/data/uploads
ENV PORT=80

EXPOSE 80

CMD set -eu; \
    mkdir -p "$UPLOAD_DIR"; \
    if [ -e /var/www/html/uploads ] && [ ! -L /var/www/html/uploads ]; then \
      if [ -d /var/www/html/uploads ]; then \
        cp -an /var/www/html/uploads/. "$UPLOAD_DIR/" 2>/dev/null || true; \
        rm -rf /var/www/html/uploads; \
      else \
        rm -f /var/www/html/uploads; \
      fi; \
    fi; \
    ln -sfn "$UPLOAD_DIR" /var/www/html/uploads; \
    php /var/www/html/scripts/init_db.php; \
    echo "Starting PHP server on 0.0.0.0:${PORT}"; \
    exec php -S "0.0.0.0:${PORT}" -t /var/www/html
