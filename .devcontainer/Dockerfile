FROM mcr.microsoft.com/devcontainers/php:0-8.1

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev

# install php extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    xml \
    zip \
    bcmath \
    pcntl

# disable xdebug
RUN rm -f /usr/local/etc/php/conf.d/xdebug.ini

# set memory limit
RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
