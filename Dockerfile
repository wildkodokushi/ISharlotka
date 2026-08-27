FROM php:7.4-apache

# Устанавливаем расширения PDO и MySQLi для работы с базой данных MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включаем модуль rewrite для поддержки ЧПУ (.htaccess)
RUN a2enmod rewrite

# Копируем все файлы вашего проекта внутрь контейнера
COPY . /var/www/html/

# Открываем стандартный порт веб-сервера
EXPOSE 80
