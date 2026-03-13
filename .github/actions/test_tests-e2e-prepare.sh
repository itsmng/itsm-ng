#!/bin/bash -e

export PLAYWRIGHT_BASE_URL="${PLAYWRIGHT_BASE_URL:-http://app-web:8088}"
export PLAYWRIGHT_APP_TOKEN_FILE="${PLAYWRIGHT_APP_TOKEN_FILE:-tests/files/_playwright/app-token}"

bin/console itsmng:config:set --config-dir=./tests/config enable_api 1
bin/console itsmng:config:set --config-dir=./tests/config enable_api_login_credentials 1
bin/console itsmng:config:set --config-dir=./tests/config url_base_api "$PLAYWRIGHT_BASE_URL/apirest.php"

php tests/e2e/prepare_api_client.php "$PLAYWRIGHT_APP_TOKEN_FILE"
