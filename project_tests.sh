#!/usr/bin/env bash
set -e

rm -f build/*.xml

echo "proofreader"
proofreader bin/ src/ web/
proofreader --no-phpcpd tests/

echo "PHPUnit tests"
vendor/bin/phpunit --log-junit build/phpunit.xml
