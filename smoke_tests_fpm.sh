#!/bin/bash
set -e
set -o pipefail

assert_fpm "/ping" "pong"
