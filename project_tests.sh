#!/usr/bin/env bash
set -e

rm -f build/*.xml

echo "phpcs"
vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p bin/ src/ web/
vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p tests/

echo "PHPUnit tests"
vendor/bin/phpunit --log-junit build/phpunit.xml
