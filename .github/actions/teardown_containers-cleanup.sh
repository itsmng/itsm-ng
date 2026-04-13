#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname "$0")/../..")

echo "Cleanup containers and volumes"
"$ROOT_DIR/.github/actions/docker-compose.sh" down --volumes
