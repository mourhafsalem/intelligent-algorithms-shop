FROM php:8.2-apache

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chow -R www-data:www-data /var/www/html

EXPOSE 80
