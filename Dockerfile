FROM silintl/ssp-base:latest

MAINTAINER Phillip Shipley <phillip.shipley@gmail.com>

ENV REFRESHED_AT 2016-12-8

# Create required directories
RUN mkdir -p /data

WORKDIR /data

# Install/cleanup composer dependencies
COPY composer.json /data/
COPY composer.lock /data/
RUN composer install --prefer-dist --no-interaction --no-dev --optimize-autoloader --no-scripts
# RUN composer update  # Only use if you need to overwrite the composer.lock file

EXPOSE 80