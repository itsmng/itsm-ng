#!/bin/bash -e

for required_file in tests/config/config_db.php tests/config/glpicrypt.key; do
  if [[ ! -f "$required_file" ]]; then
    echo "Missing required test install file: $required_file"
    echo
    echo "run: bin/console itsmng:database:install --config-dir=tests/config ..."
    echo
    exit 1
  fi
done

export PLAYWRIGHT_BASE_URL="${PLAYWRIGHT_BASE_URL:-http://app-web:8088}"
export PLAYWRIGHT_HTML_OPEN=never
export PLAYWRIGHT_APP_TOKEN_FILE="${PLAYWRIGHT_APP_TOKEN_FILE:-tests/files/_playwright/app-token}"

mkdir -p tests/files/_playwright

SERVER_READY=false
for _ in $(seq 1 30); do
  if node -e "fetch(process.argv[1]).then(response => process.exit(response.ok ? 0 : 1)).catch(() => process.exit(1))" "$PLAYWRIGHT_BASE_URL/index.php"; then
    SERVER_READY=true
    break
  fi
  sleep 1
done

if [[ "$SERVER_READY" != "true" ]]; then
  echo "PHP test server did not become ready at $PLAYWRIGHT_BASE_URL"
  exit 1
fi

if [[ ! -f "$PLAYWRIGHT_APP_TOKEN_FILE" ]]; then
  echo "Missing E2E API client token file: $PLAYWRIGHT_APP_TOKEN_FILE"
  exit 1
fi

export PLAYWRIGHT_APP_TOKEN=$(cat "$PLAYWRIGHT_APP_TOKEN_FILE")
if [[ -z "$PLAYWRIGHT_APP_TOKEN" ]]; then
  echo "E2E API client token file is empty: $PLAYWRIGHT_APP_TOKEN_FILE"
  exit 1
fi

npx playwright test -c tests/e2e/playwright.config.mts
