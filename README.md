# The Daily Panel — GitHub + Railway ready

Этот репозиторий уже подготовлен для публикации через GitHub на Railway.

## Что сохраняется

- Новости, категории и администратор — в MySQL.
- Загруженные фотографии — в Railway Volume.
- Код — в GitHub.
- Новый push в подключённую ветку GitHub автоматически запускает новый deploy Railway.

## Вход в редакцию

- Логин: `admin`
- Пароль: `admin`
- Адрес: `/admin/login.php`

Для публичного сайта лучше сразу задать собственный пароль через переменную `ADMIN_PASSWORD`.

## Развёртывание

### 1. GitHub

Создайте новый репозиторий и загрузите в него **содержимое этой папки**, чтобы `Dockerfile` находился в корне репозитория.

### 2. Railway

1. Создайте новый Project.
2. Добавьте **MySQL**.
3. Добавьте сервис **Deploy from GitHub repo** и выберите этот репозиторий.
4. В Variables веб-сервиса добавьте ссылки на переменные MySQL:

```text
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
ADMIN_LOGIN=admin
ADMIN_PASSWORD=admin
UPLOAD_DIR=/data/uploads
```

Если ваш MySQL-сервис называется не `MySQL`, используйте его фактическое имя вместо `MySQL` в ссылках.

### 3. Постоянные фотографии

У веб-сервиса создайте Railway Volume и установите:

```text
Mount Path: /data
```

Тогда фото, загруженные через админку, сохраняются в `/data/uploads` и не исчезают после нового deploy.

### 4. Публичный адрес

В настройках веб-сервиса откройте Networking → **Generate Domain**.

После этого:
- сайт: `https://ВАШ-ДОМЕН/`
- админка: `https://ВАШ-ДОМЕН/admin/login.php`

## Автоматическая база

При каждом запуске контейнер выполняет `scripts/init_db.php`.

Скрипт:
- ждёт готовности MySQL;
- создаёт таблицы, если их ещё нет;
- создаёт рубрики;
- создаёт/обновляет администратора;
- добавляет демонстрационную статью только если база публикаций пустая.

Обычные новости при redeploy не удаляются.

## Локальный запуск Docker

Если у вас есть доступный MySQL:

```bash
docker build -t retro-press .
docker run --rm -p 8080:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=3306 \
  -e DB_NAME=retro_press \
  -e DB_USER=root \
  -e DB_PASS=YOUR_PASSWORD \
  -e ADMIN_LOGIN=admin \
  -e ADMIN_PASSWORD=admin \
  retro-press
```

Откройте `http://localhost:8080`.

## Важно

GitHub сам по себе не выполняет PHP и не предоставляет MySQL. GitHub в этой схеме хранит исходный код, а Railway запускает PHP-сайт и базу данных.

Для публичного запуска замените `ADMIN_PASSWORD=admin` на сильный пароль.
