#!/usr/bin/env bash
set -e

docker run \
    -v $(pwd):/srv/annotations \
    -v /srv/annotations/var/logs \
    -t \
    annotations-cli \
    /usr/bin/env php /srv/annotations/vendor/bin/phpunit
