#!/bin/bash
set -e

ping=$(curl_fpm /ping | tail -n 1)
echo "GET /ping: $ping"
[ "$ping" == "pong" ]

