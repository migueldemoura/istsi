FROM composer/composer

RUN usermod -u 1000 www-data

RUN mkdir -p /composer/ vendor && chown -R www-data /composer/ vendor

WORKDIR /var/www/istsi

CMD ["composer"]