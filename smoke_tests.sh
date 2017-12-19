#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh
set -e

bin/console --version

host="${1:-$(hostname)}"
port="${2:-80}"

# smoke tests need to run while allowing errors
set +e
curl -v "$host:$port/ping"
smoke_url_ok "$host:$port/ping"
    smoke_assert_body "pong"

smoke_report
