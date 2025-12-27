#syntax=docker/dockerfile:1.4

# Versions definition
ARG PHP_VERSION=8.3
ARG FRANKENPHP_VERSION=1.1
ARG SYMFONY_VERSION=7.0
ARG API_PLATFORM_VERSION=3.2

# Base FrankenPHP image
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-alpine AS frankenphp_upstream

# The different stages of this Dockerfile are designed to minimize the size of the final image
# and minimize the time it takes to rebuild the image during development.

FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# persistent / runtime deps
# hadolint ignore=DL3018
RUN apk add --no-cache \
		acl \
		file \
		gettext \
		git \
	;

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
        pdo_pgsql \
        redis \
	;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --link api/composer.* api/symfony.* ./
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress -n --ignore-platform-reqs

COPY --link api .

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;
