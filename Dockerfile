FROM php:7.0.26-fpm-jessie

RUN apt-get update && apt-get install -y git-core zip
# replace with multi-stage build using the composer official image?
RUN curl https://getcomposer.org/download/1.5.2/composer.phar -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer 

WORKDIR /srv/annotations
COPY composer.json composer.lock /srv/annotations/
RUN composer install

COPY . /srv/annotations

