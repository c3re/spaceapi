FROM debian:13
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y \
  php-cli php-json \
  php-pear php-dev \
  libmosquitto-dev \
  libmosquitto1 \
  mosquitto-clients \
  php-yaml \
  locales-all \
  git


WORKDIR /opt
RUN git clone https://github.com/nismoryco/Mosquitto-PHP.git
WORKDIR /opt/Mosquitto-PHP
RUN phpize
RUN ./configure
RUN make
RUN make install
RUN echo "extension=mosquitto.so" >/etc/php/8.4/mods-available/mosquitto.ini
RUN ln -s /etc/php/8.4/mods-available/mosquitto.ini /etc/php/8.4/cli/conf.d/20-mosquitto.ini

ADD . /app

VOLUME /app/public

ENTRYPOINT /app/generator.php
