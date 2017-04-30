FROM nginx

ARG HOST

RUN usermod -u 1000 www-data

COPY nginx.conf /etc/nginx/
COPY istsi.conf /istsi.conf
RUN envsubst '${HOST}' < /istsi.conf > /istsi.conf.new && mv /istsi.conf.new /etc/nginx/conf.d/istsi.conf
RUN rm /etc/nginx/conf.d/default.conf

COPY tls/istsi.pem /etc/nginx/ssl/istsi.pem
COPY tls/istsi.key /etc/nginx/ssl/istsi.key

CMD ["nginx"]
