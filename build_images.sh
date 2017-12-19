#!/bin/sh

docker build -f Dockerfile.fpm -t annotations_fpm .
docker build -f Dockerfile.cli -t annotations_cli .
docker build -f Dockerfile.ci -t annotations_ci .
