#!/bin/sh
set -eu

PERSISTENT_UPLOAD_DIR="${UPLOAD_DIR:-/data/uploads}"
APP_PORT="${PORT:-80}"

mkdir -p "$PERSISTENT_UPLOAD_DIR"

if [ -e /var/www/html/uploads ] && [ ! -L /var/www/html/uploads ]; then
  if [ -d /var/www/html/uploads ]; then
    cp -an /var/www/html/uploads/. "$PERSISTENT_UPLOAD_DIR/" 2>/dev/null || true
    rm -rf /var/www/html/uploads
  else
    rm -f /var/www/html/uploads
  fi
fi

ln -sfn "$PERSISTENT_UPLOAD_DIR" /var/www/html/uploads

php /var/www/html/scripts/init_db.php

echo "Starting PHP server on 0.0.0.0:${APP_PORT}"
exec php -S "0.0.0.0:${APP_PORT}" -t /var/www/html
