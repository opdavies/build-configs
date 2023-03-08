FROM php:8.1-cli

WORKDIR /app

RUN apt-get update -yqq \
  && apt-get install -yqq git unzip

ENV PATH="${PATH}:/app/vendor/bin"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.* ./

RUN composer install

COPY . .

ENTRYPOINT ["bash"]
