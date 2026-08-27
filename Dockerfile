FROM php:7.4-fpm-alpine

# Устанавливаем Nginx, Supervisor и расширения MySQL
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем абсолютно все файлы вашего проекта в контейнер
COPY . /var/www/html/

# Настраиваем конфигурацию Nginx с разрешением внешних доменов хостинга
RUN printf 'server { \n\
    listen 80 default_server; \n\
    listen [::]:80 default_server; \n\
    server_name _; \n\
    root /var/www/html; \n\
    index index.php index.html; \n\
    location / { \n\
        try_files $uri $uri/ /index.php?$query_string; \n\
    } \n\
    location ~ \.php$ { \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_index index.php; \n\
        include fastcgi_params; \n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \n\
    } \n\
}\n' > /etc/nginx/http.d/default.conf

# Заменяем стандартный конфиг supervisor нашим локальным файлом
COPY supervisord.conf /etc/supervisord.conf

# Открываем порт 80
EXPOSE 80

# Запускаем через supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
