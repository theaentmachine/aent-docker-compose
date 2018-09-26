FROM thecodingmachine/php:7.2-v1-cli as builder

COPY composer.json composer.json
COPY composer.lock composer.lock

ENV PHP_EXTENSION_INTL=1

RUN composer install --no-dev

FROM theaentmachine/base-php-aent:0.0.24

# Installs Docker Compose.
RUN pip3 install --upgrade --no-cache-dir docker-compose

# Copies our aent entry point.
COPY aent.sh /usr/bin/aent

# Copies vendor directory.
COPY --from=builder /usr/src/app/vendor ./vendor

# Copies our PHP source.
COPY ./src ./src