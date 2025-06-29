FROM ghcr.io/roadrunner-server/roadrunner:2024 as roadrunner

FROM php:8.4-alpine as base

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN --mount=type=bind,from=mlocati/php-extension-installer:2,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    apk add --no-cache libtool autoconf g++ make protobuf-dev protobuf \
    && install-php-extensions \
        @composer-2 \
        opcache \
        zip \
        bcmath \
        sockets \
        pcntl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        amqp \
        redis \
        msgpack \
        grpc \
        intl \
        && curl -LO https://github.com/protocolbuffers/protobuf/releases/download/v24.4/protoc-24.4-linux-x86_64.zip \
        && unzip protoc-24.4-linux-x86_64.zip -d /usr/local \
        && rm protoc-24.4-linux-x86_64.zip

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY ./composer.* ./
RUN composer install --no-dev --optimize-autoloader || true

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN addgroup -g ${WWWGROUP} appuser \
    && adduser -u ${WWWUSER} -G appuser -s /bin/sh -D appuser

FROM base AS prod

COPY ./composer.* ./
RUN composer install --no-dev --optimize-autoloader || true

COPY . .

USER appuser

EXPOSE 8080

CMD ["rr", "serve", "-c", ".rr.prod.yaml"]

FROM base AS dev

COPY ./composer.* ./
RUN composer install

COPY . .

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && apk del .build-deps

USER appuser

EXPOSE 8080 9003

CMD ["rr", "serve", "-c", ".rr.yaml"]