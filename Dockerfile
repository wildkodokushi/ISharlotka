FROM php:7.4-apache

# Устанавливаем расширения для работы с MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включаем модуль rewrite для поддержки .htaccess
RUN a2enmod rewrite

# Важнейший хак для Railway: заменяем жесткий порт 80 на переменную среды PORT во всех конфигах Apache
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Копируем все файлы проекта внутрь контейнера
COPY . /var/www/html/
