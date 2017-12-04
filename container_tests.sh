#!/usr/bin/env bash
set -e

docker run -it annotations /usr/bin/env php /srv/annotations/vendor/bin/phpunit
