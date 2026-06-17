# Production deployment

Инструкция рассчитана на сервер Linux с Docker Engine и Docker Compose plugin. Проект разворачивается из GitHub, приложение, MySQL и phpMyAdmin запускаются отдельными контейнерами.

## 1. Подготовить сервер

Установите Docker Engine и Docker Compose plugin по официальной инструкции Docker:

- Docker Engine Ubuntu: https://docs.docker.com/engine/install/ubuntu/
- Docker Compose plugin: https://docs.docker.com/compose/install/linux/

Проверьте установку:

```bash
docker --version
docker compose version
```

## 2. Склонировать проект из GitHub

```bash
cd /var/www
git clone https://github.com/zhandos998/teachai.atu.kz.git
cd teachai.atu.kz
```

Для обновления уже развернутого проекта:

```bash
cd /var/www/teachai.atu.kz
git pull origin main
```

## 3. Создать production env

```bash
cp .env.docker.example .env.docker
nano .env.docker
```

Обязательно задайте реальные значения:

- `APP_KEY`
- `APP_URL`
- `DB_PASSWORD`
- `MYSQL_ROOT_PASSWORD` (желательно отдельный пароль; если не задан, Docker Compose использует `DB_PASSWORD`)
- `MAIL_*`
- `OPENAI_API_KEY`

Сгенерировать `APP_KEY` можно без установленного PHP на сервере:

```bash
docker run --rm php:8.3-cli php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
```

Секреты нельзя коммитить в Git. Файл `.env.docker` уже находится в `.gitignore`.

## 4. Запустить контейнеры

```bash
docker compose --env-file .env.docker -f docker-compose.prod.yml up -d --build
```

Проверить состояние:

```bash
docker compose --env-file .env.docker -f docker-compose.prod.yml ps
docker compose --env-file .env.docker -f docker-compose.prod.yml logs -f app
```

Миграции запускаются автоматически при старте контейнера `app`. Если нужны сиды:

```bash
docker compose --env-file .env.docker -f docker-compose.prod.yml exec app php artisan db:seed --force
```

## 5. Доступы

По умолчанию сервисы слушают только localhost сервера:

- приложение: `127.0.0.1:8081`
- MySQL: `127.0.0.1:3308`
- phpMyAdmin: `127.0.0.1:8082`

Для публичного домена поставьте Nginx/Apache на сервере как reverse proxy на `http://127.0.0.1:8081` и выпустите SSL-сертификат. phpMyAdmin лучше не открывать в интернет; используйте SSH tunnel:

```bash
ssh -L 8082:127.0.0.1:8082 user@server
```

После этого phpMyAdmin будет доступен локально на `http://127.0.0.1:8082`.

## 6. Обновление production

```bash
cd /var/www/teachai.atu.kz
git pull origin main
docker compose --env-file .env.docker -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.docker -f docker-compose.prod.yml exec app php artisan migrate --force
```

## 7. Полезные команды

```bash
docker compose --env-file .env.docker -f docker-compose.prod.yml logs -f
docker compose --env-file .env.docker -f docker-compose.prod.yml exec app php artisan about
docker compose --env-file .env.docker -f docker-compose.prod.yml exec mysql mysql -u teachai -p teachai
docker compose --env-file .env.docker -f docker-compose.prod.yml down
```
