#!/bin/bash
set -e

docker-compose down --volumes
docker rmi ${COMPOSE_PROJECT_NAME}_apache ${COMPOSE_PROJECT_NAME}_php ${COMPOSE_PROJECT_NAME}_mailhog ${COMPOSE_PROJECT_NAME}_phpmyadmin ${COMPOSE_PROJECT_NAME}_mysql
