#!/bin/bash -e

export PLAYWRIGHT_BASE_URL="${PLAYWRIGHT_BASE_URL:-http://app-web:8088}"
export PLAYWRIGHT_APP_TOKEN_FILE="${PLAYWRIGHT_APP_TOKEN_FILE:-tests/files/_playwright/app-token}"

mkdir -p \
  tests/files/_cache/cache_db \
  tests/files/_cache/cache_trans \
  tests/files/_cron \
  tests/files/_dumps \
  tests/files/_graphs \
  tests/files/_locales \
  tests/files/_lock \
  tests/files/_log \
  tests/files/_pictures \
  tests/files/_playwright \
  tests/files/_plugins \
  tests/files/_rss \
  tests/files/_sessions \
  tests/files/_tmp \
  tests/files/_uploads

bin/console itsmng:config:set --config-dir=./tests/config enable_api 1
bin/console itsmng:config:set --config-dir=./tests/config enable_api_login_credentials 1
bin/console itsmng:config:set --config-dir=./tests/config use_notifications 1
bin/console itsmng:config:set --config-dir=./tests/config notifications_mailing 1
bin/console itsmng:config:set --config-dir=./tests/config url_base_api "$PLAYWRIGHT_BASE_URL/apirest.php"

php tests/e2e/prepare_api_client.php "$PLAYWRIGHT_APP_TOKEN_FILE"
