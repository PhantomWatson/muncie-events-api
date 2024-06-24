#!/bin/bash
set -e

docker-compose down --volumes
docker rmi muncieevents4-apache muncieevents4-php muncieevents4-mysql
