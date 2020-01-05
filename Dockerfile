# docker build . -t username/bootstrapi:dev
# docker push username/bootstrapi:dev

FROM php:7.2-fpm
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/log
