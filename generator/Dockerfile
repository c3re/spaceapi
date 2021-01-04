FROM debian:10
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y \
    php7.3-cli php-json  \
    php-pear php-dev \
    libmosquitto-dev libmosquitto1 mosquitto-clients \
    php-yaml \
    locales-all
RUN pecl install Mosquitto-alpha
RUN echo "extension=mosquitto.so" >/etc/php/7.3/mods-available/mosquitto.ini
RUN ln -s /etc/php/7.3/mods-available/mosquitto.ini /etc/php/7.3/cli/conf.d/20-mosquitto.ini
ADD .. /app

VOLUME /app/public


ENTRYPOINT /app/generator.php
