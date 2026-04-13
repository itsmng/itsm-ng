#!/bin/bash -e

PHP_MAJOR_VERSION="$(echo ${PHP_VERSION:-$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')} | cut -d '.' -f 1,2)"
WORKTREE_DIR="$(pwd)"

# The repository is bind-mounted into the container, so Git sees host ownership.
# Mark it as safe before Composer tries to inspect VCS metadata.
git config --global --add safe.directory "$WORKTREE_DIR"

# Validate composer config
composer validate --strict
if ! [[ "$PHP_MAJOR_VERSION" == "8.0" ]] && [[ -f composer.lock ]]; then
  composer check-platform-reqs;
elif ! [[ -f composer.lock ]]; then
  echo "composer.lock not found, skipping composer check-platform-reqs"
fi

# Install dependencies
if [[ "$PHP_MAJOR_VERSION" == "8.0" ]]; then
  COMPOSER_ADD_OPTS=--ignore-platform-reqs;
fi
bin/console dependencies install --composer-options="$COMPOSER_ADD_OPTS --prefer-dist --no-progress"
