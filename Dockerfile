FROM php:8.2-apache

WORKDIR /var/www/html
COPY . .

RUN chmod -R 755 /var/www/html
RUN a2enmod rewrite

EXPOSE 80
