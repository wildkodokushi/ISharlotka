FROM php:7.4-fpm-alpine

# Устанавливаем Nginx, Supervisor и расширения MySQL
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . /var/www/html/

# Создаем шаблон конфига Nginx (вместо порта пишем %PORT%)
RUN echo 'server { \
    listen [%PORT%] default_server; \
    listen %PORT% default_server; \
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
}' > /etc/nginx/http.d/default.conf.template

# Настраиваем Supervisor, который перед запуском Nginx подставит реальный порт из переменной $PORT
RUN echo '[supervisord]\n\
nodaemon=true\n\
user=root\n\
[program:php-fpm]\n\
command=php-fpm\n\
[program:nginx]\n\
command=sh -c "sed -i \"s/%PORT%/${PORT}/g\" /etc/nginx/http.d/default.conf.template && cp /etc/nginx/http.d/default.conf.template /etc/nginx/http.d/default.conf && nginx -g \"daemon off;\""' > /etc/supervisord.conf

# Запускаем через supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
