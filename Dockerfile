FROM php:7.4-fpm-alpine

# Устанавливаем Nginx, Supervisor для запуска процессов и расширения MySQL
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем абсолютно все файлы вашего проекта в контейнер
COPY . /var/www/html/

# Создаем конфигурацию Nginx, которая будет слушать динамический порт Railway
RUN echo 'server { \
    listen [::]:8080 default_server; \
    listen 8080 default_server; \
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

# Настраиваем Supervisor для одновременного контроля за Nginx и PHP
RUN echo '[supervisord]\n\
nodaemon=true\n\
user=root\n\
[program:php-fpm]\n\
command=php-fpm\n\
[program:nginx]\n\
command=nginx -g "daemon off;"' > /etc/supervisord.conf

# Сообщаем хостингу порт
EXPOSE 8080

# Запускаем менеджер процессов
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
