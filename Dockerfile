FROM php:7.4-fpm-alpine

# Устанавливаем Nginx, Supervisor и расширения MySQL
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем абсолютно все файлы вашего проекта в контейнер
COPY . /var/www/html/

# Создаем шаблон конфигурации Nginx с меткой _PORT_
RUN printf 'server { \n\
    listen _PORT_ default_server; \n\
    listen [::]:_PORT_ default_server; \n\
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
}\n' > /etc/nginx/http.d/default.conf.template

# Заменяем стандартный конфиг supervisor на запуск скрипта подстановки порта
RUN printf '[supervisord]\n\
nodaemon=true\n\
user=root\n\
[program:php-fpm]\n\
command=php-fpm\n\
[program:nginx]\n\
command=sh -c "sed \"s/_PORT_/${PORT}/g\" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf && nginx -g \"daemon off;\""\n' > /etc/supervisord.conf

# Запускаем через supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
