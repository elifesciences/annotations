#!/usr/bin/env bash
set -e

rm -f build/*.xml

echo "proofreader"
proofreader bin/ src/ tests/ web/

echo "PHPUnit tests"
vendor/bin/phpunit --log-junit build/phpunit.xml
