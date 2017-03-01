FROM debian:jessie

RUN apt update && apt install -y nginx

ADD nginx.conf /etc/nginx/
ADD istsi.conf /etc/nginx/sites-available/

ADD tls/istsi.pem /etc/nginx/ssl/istsi.pem
ADD tls/istsi.key /etc/nginx/ssl/istsi.key

RUN ln -s /etc/nginx/sites-available/istsi.conf /etc/nginx/sites-enabled/istsi
RUN rm /etc/nginx/sites-enabled/default

RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

CMD ["nginx"]

EXPOSE 443
