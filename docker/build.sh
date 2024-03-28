#!/bin/bash
set -e

if ! [[ -d ../logs/apache ]]; then
    mkdir -p ../logs/docker/apache
fi

if ! [[ -d ../logs/mysql ]]; then
    mkdir -p ../logs/docker/mysql
fi

if ! [[ -d ../logs/php ]]; then
    mkdir -p ../logs/docker/php
fi

if ! [[ -d ../database ]]; then
    mkdir ../database
fi

docker-compose up -d --build

docker exec muncieevents4_apache chown -R root:www-data /usr/local/apache2/logs
docker exec muncieevents4_php chown -R root:www-data /usr/local/etc/logs
