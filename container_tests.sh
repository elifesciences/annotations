#!/usr/bin/env bash
set -e

docker run -t annotations-cli /usr/bin/env php /srv/annotations/vendor/bin/phpunit
