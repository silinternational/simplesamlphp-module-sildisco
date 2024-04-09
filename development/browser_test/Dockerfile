FROM silintl/php8:8.1
LABEL maintainer="Matt Henderson <matt_henderson@sil.org>"

ENV REFRESHED_AT 2022-01-03

RUN mkdir -p /data
WORKDIR /data

COPY ./composer.json /data/
COPY ./composer.lock /data/

RUN composer install --prefer-dist --no-interaction --optimize-autoloader
