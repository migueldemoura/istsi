FROM php:7.1-fpm

RUN apt update && apt install -y git unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/London /etc/localtime
RUN "date"

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/istsi
