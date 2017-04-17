FROM silintl/php7:latest
MAINTAINER Phillip Shipley <phillip_shipley@sil.org>

ENV REFRESHED_AT 2017-04-11

RUN mkdir -p /data
WORKDIR /data

COPY ./composer.json /data/
COPY ./composer.lock /data/
COPY ./run.sh /data/

RUN composer install --prefer-dist --no-interaction --optimize-autoloader

RUN ln -s /data/vendor/codeception/codeception/codecept /data/codecept

EXPOSE 80
CMD ["/data/run.sh"]