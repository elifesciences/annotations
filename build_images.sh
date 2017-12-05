#!/bin/sh

docker build -f Dockerfile.fpm -t annotations-fpm .
docker build -f Dockerfile.cli -t annotations-cli .
