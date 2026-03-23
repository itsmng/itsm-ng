#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname $0)/../..")

if [[ "${DB_TYPE:-mysql}" = "pgsql" ]]; then
  echo "Skipping initialization of legacy MySQL fixtures on PostgreSQL"
  exit 0
fi

echo "Initialize old versions databases"
docker-compose exec -T db mysql --user=root --execute="DROP DATABASE IF EXISTS \`glpitest0723\`;"
docker-compose exec -T db mysql --user=root --execute="CREATE DATABASE \`glpitest0723\`;"
cat $ROOT_DIR/tests/glpi-0.72.3-empty.sql | docker-compose exec -T db mysql --user=root glpitest0723
