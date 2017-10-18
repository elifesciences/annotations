#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh

bin/console --version --env=$ENVIRONMENT_NAME

smoke_url_ok $(hostname)/ping
    smoke_assert_body "pong"

smoke_report
