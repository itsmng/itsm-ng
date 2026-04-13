#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname $0)/../..")
COMPOSE_CMD="$ROOT_DIR/.github/actions/docker-compose.sh"

if [[ "${DB_TYPE:-mysql}" = "pgsql" ]]; then
  echo "Skipping initialization of legacy MySQL fixtures on PostgreSQL"
  exit 0
fi

echo "Initialize old versions databases"
"$COMPOSE_CMD" exec -T db mysql --user=root --execute="DROP DATABASE IF EXISTS \`glpitest0723\`;"
"$COMPOSE_CMD" exec -T db mysql --user=root --execute="CREATE DATABASE \`glpitest0723\`;"
cat $ROOT_DIR/tests/glpi-0.72.3-empty.sql | "$COMPOSE_CMD" exec -T db mysql --user=root glpitest0723
