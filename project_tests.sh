#!/usr/bin/env bash
set -e

rm -f build/*.xml

echo "cache:clear"
bin/console cache:clear --env=test --no-warmup

echo "security:check"
bin/console security:check

echo "proofreader"
vendor/bin/proofreader app/ bin/ src/ web/
vendor/bin/proofreader --no-phpcpd tests/

echo "PHPSpec tests"
vendor/bin/phpspec run --format=junit | tee build/phpspec.xml

echo "PHPUnit tests"
vendor/bin/phpunit --log-junit build/phpunit.xml
