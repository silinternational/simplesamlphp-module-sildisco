FROM silintl/php8:8.1
LABEL maintainer="Matt Henderson <matt_henderson@sil.org>"

ENV REFRESHED_AT 2022-01-03

RUN mkdir -p /data
WORKDIR /data

COPY ./composer.json /data/
COPY ./composer.lock /data/
COPY ./run.sh /data/

RUN chmod a+x /data/run.sh
RUN composer self-update --no-interaction
RUN composer update --no-interaction
RUN composer install --prefer-dist --no-interaction --optimize-autoloader

RUN ln -s /data/vendor/codeception/codeception/codecept /data/codecept

EXPOSE 80
CMD ["/data/run.sh"]
