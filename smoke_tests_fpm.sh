#!/bin/bash
set -e

# TODO: we should need only one web/app.php file?
# use $ENVIRONMENT_NAME with a default?
ping=$(SCRIPT_FILENAME=/srv/annotations/web/app_dev.php/ping REQUEST_URI=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | tail -n 1)
echo "GET /ping: $ping"
[ "$ping" == "pong" ]

