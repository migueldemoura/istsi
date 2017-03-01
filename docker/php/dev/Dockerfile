FROM php:7.1-fpm

RUN apt update && apt install -y git unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/London /etc/localtime
RUN "date"

RUN docker-php-ext-install pdo pdo_mysql

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN echo "error_reporting = E_ALL \n \
display_startup_errors=1 \n \
display_errors=1 \n \
xdebug.remote_enable=1 \n \
xdebug.remote_connect_back=1 \n \
xdebug.remote_autostart=1 \n \
xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/istsi
