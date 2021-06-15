FROM php:7.4-fpm

#RUN apt-get update \
#    && apt-get -y --no-install-recommends install  php7.4-mysql php7.4-intl php7.4-mbstring php7.4-sqlite3\
#    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions \
    bcmath \
    bz2 \
    curl \
    fileinfo \
    gd \
    intl \
    mbstring \
    mysqlnd \
    pdo \
    simplexml \
    zip

RUN install-php-extensions @composer

RUN pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR "/var/www/muncie-events"
