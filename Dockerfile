# docker build . -t sunsingerus/bootstrapi:0.0.1
# docker push sunsingerus/bootstrapi:0.0.1

FROM php:7.2-fpm
COPY . /var/www/html
