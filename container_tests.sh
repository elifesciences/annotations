#!/usr/bin/env bash
set -e

docker run -it annotations-cli /usr/bin/env php /srv/annotations/vendor/bin/phpunit
