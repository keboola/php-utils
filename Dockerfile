FROM php:7.4-cli

RUN apt-get update -q \
  && apt-get install wget unzip git -y --no-install-recommends \
  locales \
  && sed -i 's/^# *\(en_US.UTF-8\)/\1/' /etc/locale.gen \
  && locale-gen

ENV LANGUAGE=en_US.UTF-8
ENV LANG=en_US.UTF-8
ENV LC_ALL=en_US.UTF-8

COPY composer-install.sh composer-install.sh

RUN ./composer-install.sh \
  && mv composer.phar /usr/local/bin/composer

COPY . /code

WORKDIR /code

RUN composer install
