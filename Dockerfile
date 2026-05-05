FROM php:8.2-apache

RUN a2enmod rewrite

COPY . /var/www/php/

EXPOSE 80
