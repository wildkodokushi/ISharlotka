FROM php:7.4-cli-alpine

# Устанавливаем расширения для работы с MySQL базы данных
RUN apk add --no-cache $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем абсолютно все файлы вашего проекта в контейнер
COPY . /var/www/html/

# Открываем порт 8080
EXPOSE 8080

# Запускаем встроенный веб-сервер PHP на порту 8080, который поймет Railway
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
