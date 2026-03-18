#!/bin/bash -e

if command -v docker-compose >/dev/null 2>&1; then
  exec docker-compose "$@"
fi

if docker compose version >/dev/null 2>&1; then
  exec docker compose "$@"
fi

echo "This script requires either \"docker-compose\" or \"docker compose\"." >&2
exit 1
