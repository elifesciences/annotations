#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh
set -e

bin/console --version

# smoke tests need to run while allowing errors
set +e
smoke_url_ok $(hostname)/ping
    smoke_assert_body "pong"

smoke_report
