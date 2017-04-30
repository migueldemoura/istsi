FROM nginx

ARG SERVER_NAME

RUN usermod -u 1000 www-data

COPY nginx.conf /etc/nginx/
COPY istsi.conf /istsi.conf
RUN envsubst '${SERVER_NAME}' < /istsi.conf > /etc/nginx/conf.d/istsi.conf && rm /istsi.conf
RUN rm /etc/nginx/conf.d/default.conf

COPY tls/istsi.pem /etc/nginx/ssl/istsi.pem
COPY tls/istsi.key /etc/nginx/ssl/istsi.key

CMD ["nginx"]
