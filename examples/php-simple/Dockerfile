FROM php:8.1-cli-bullseye AS base

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN which composer && composer -V

WORKDIR /app

ENV PATH="${PATH}:/app/vendor/bin"

COPY composer.* ./




