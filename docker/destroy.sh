#!/bin/bash
set -e

docker-compose down --volumes
docker rmi muncie_events_apache muncie_events_php
