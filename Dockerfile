FROM php:7.4-cli-alpine

# Устанавливаем расширения для работы с MySQL
RUN apk add --no-cache $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . /var/www/html/

# Открываем порт 8080
EXPOSE 8080

# СТАНДАРТНЫЙ ЗАПУСК: разрешаем PHP открывать любые файлы по их реальным путям
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
