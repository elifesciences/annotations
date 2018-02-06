#!/bin/bash
set -e
set -o pipefail

# TODO: specify expected output in call to curl_fpm, rename assert_fpm
ping=$(curl_fpm /ping | tail -n 1)
echo "GET /ping: $ping"
[ "$ping" == "pong" ]
