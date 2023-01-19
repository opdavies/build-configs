FROM php:8.1-cli-bullseye AS base

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN which composer && composer -V

WORKDIR /app

ENV PATH="${PATH}:/app/vendor/bin"

COPY composer.* ./


################################################################################

FROM base AS build


RUN apt-get update -yqq \
  && apt-get install -yqq --no-install-recommends \
    git unzip


RUN composer install




################################################################################

FROM base AS test

COPY . .

RUN phpunit --testdox


