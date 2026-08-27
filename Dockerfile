FROM php:7.4-fpm-alpine

# Устанавливаем Nginx, Supervisor и расширения MySQL
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем абсолютно все файлы вашего проекта в контейнер
COPY . /var/www/html/

# Настраиваем конфигурацию Nginx строго на порт 8080 (для IPv4 и IPv6)
RUN echo 'server { \
    listen 8080 default_server; \
    listen [::]:8080 default_server; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/http.d/default.conf

# Заменяем стандартный конфиг supervisor нашим локальным файлом
COPY supervisord.conf /etc/supervisord.conf

# Открываем порт 8080
EXPOSE 8080

# Запускаем через supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
