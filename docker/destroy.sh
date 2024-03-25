#!/bin/bash
set -e

docker-compose down --volumes
docker rmi muncieevents_apache muncieevents_php
