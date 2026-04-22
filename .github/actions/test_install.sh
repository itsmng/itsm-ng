#!/bin/bash -e

LOG_FILE="./tests/files/_log/install.log"
mkdir -p $(dirname "$LOG_FILE")

DB_TYPE=${DB_TYPE:-mysql}
DB_HOST=${TEST_DB_HOST:-db}
DB_NAME=${TEST_DB_NAME:-glpi}
DB_USER=${TEST_DB_USER:-}
DB_PASSWORD=${TEST_DB_PASSWORD:-}

if [[ -z "$DB_USER" ]]; then
  if [[ "$DB_TYPE" = "pgsql" ]]; then
    DB_USER=postgres
  else
    DB_USER=root
  fi
fi

# Execute install
INSTALL_COMMAND=(
  bin/console itsmng:database:install
  --config-dir=./tests/config
  --ansi
  --no-interaction
  --reconfigure
  --db-type="$DB_TYPE"
  --db-name="$DB_NAME"
  --db-host="$DB_HOST"
  --db-user="$DB_USER"
  --force
)

if [[ -n "$DB_PASSWORD" ]]; then
  INSTALL_COMMAND+=(--db-password="$DB_PASSWORD")
fi

"${INSTALL_COMMAND[@]}"

# Execute update
## Should do nothing.
bin/console itsmng:database:update --config-dir=./tests/config --ansi --no-interaction | tee $LOG_FILE
if [[ -z $(grep "No migration needed." $LOG_FILE) ]];
  then echo "itsmng:database:update command FAILED" && exit 1;
fi
