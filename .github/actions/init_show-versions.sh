#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname "$0")/../..")
COMPOSE_CMD="$ROOT_DIR/.github/actions/docker-compose.sh"

"$COMPOSE_CMD" exec -T app php --version
"$COMPOSE_CMD" exec -T app php -r 'echo(sprintf("PHP extensions: %s\n", implode(", ", get_loaded_extensions())));'
"$COMPOSE_CMD" exec -T app composer --version
"$COMPOSE_CMD" exec -T app sh -c 'echo "node $(node --version)"'
"$COMPOSE_CMD" exec -T app sh -c 'echo "npm $(npm --version)"'

if [[ -n $("$COMPOSE_CMD" ps --all --services | grep "db") ]]; then
  "$COMPOSE_CMD" exec -T db mysql --version;
fi
