#!/bin/bash -e

LOG_FILE="./tests/files/_log/migration.log"
mkdir -p $(dirname "$LOG_FILE")
DB_NAME="glpitest0723"
CURRENT_ITSM_VERSION=$(grep -Po "^define\\('ITSM_VERSION', '\\K[^']+" inc/define.php | head -n 1)
CURRENT_ITSM_SCHEMA_VERSION=$(grep -Po "define\\(\"ITSM_SCHEMA_VERSION\", '\\K[^']+" inc/define.php | head -n 1)

configure_db() {
  local db_name="$1"
  bin/console itsmng:database:configure \
    --config-dir=./tests/config --ansi --no-interaction \
    --reconfigure --db-name="$db_name" --db-host=db --db-user=root
}

run_update() {
  local extra_args="${1:-}"
  # shellcheck disable=SC2086
  bin/console itsmng:database:update --config-dir=./tests/config --ansi --no-interaction --allow-unstable $extra_args | tee $LOG_FILE
}

run_update_expect_changes() {
  run_update "$1"
  if [[ -n $(grep "No migration needed." $LOG_FILE) ]]; then
    echo "bin/console itsmng:database:update command FAILED"
    exit 1
  fi
}

run_update_expect_no_changes() {
  run_update "$1"
  if [[ -z $(grep "No migration needed." $LOG_FILE) ]]; then
    echo "bin/console itsmng:database:update command FAILED"
    exit 1
  fi
}

set_baseline_version() {
  local baseline="$1"
  mysql --host=db --user=root "$DB_NAME" <<SQL
UPDATE glpi_configs SET value='${baseline}' WHERE context='core' AND name='version';
UPDATE glpi_configs SET value='${baseline}' WHERE context='core' AND name='dbversion';
UPDATE glpi_configs SET value='${baseline}' WHERE context='core' AND name='itsmversion';
UPDATE glpi_configs SET value='${baseline}' WHERE context='core' AND name='itsmdbversion';
SQL
}

assert_latest_version() {
  local actual_itsm_version
  local actual_itsm_schema_version
  actual_itsm_version=$(mysql --host=db --user=root --batch --skip-column-names "$DB_NAME" -e "SELECT value FROM glpi_configs WHERE context='core' AND name='itsmversion' LIMIT 1;")
  actual_itsm_schema_version=$(mysql --host=db --user=root --batch --skip-column-names "$DB_NAME" -e "SELECT value FROM glpi_configs WHERE context='core' AND name='itsmdbversion' LIMIT 1;")

  if [[ "$actual_itsm_version" != "$CURRENT_ITSM_VERSION" || "$actual_itsm_schema_version" != "$CURRENT_ITSM_SCHEMA_VERSION" ]]; then
    echo "Update matrix convergence FAILED (itsm=$actual_itsm_version schema=$actual_itsm_schema_version expected=$CURRENT_ITSM_VERSION/$CURRENT_ITSM_SCHEMA_VERSION)"
    exit 1
  fi
}

# Reconfigure DB
configure_db "$DB_NAME"

# Execute update
## First run should do the migration.
run_update_expect_changes
## Second run should do nothing.
run_update_expect_no_changes

# Execute myisam_to_innodb migration
## First run should do the migration.
bin/console itsmng:migration:myisam_to_innodb --config-dir=./tests/config --ansi --no-interaction | tee $LOG_FILE
if [[ -n $(grep "No migration needed." $LOG_FILE) ]];
  then echo "bin/console itsmng:migration:myisam_to_innodb command FAILED" && exit 1;
fi
## Second run should do nothing.
bin/console itsmng:migration:myisam_to_innodb --config-dir=./tests/config --ansi --no-interaction | tee $LOG_FILE
if [[ -z $(grep "No migration needed." $LOG_FILE) ]];
  then echo "bin/console itsmng:migration:myisam_to_innodb command FAILED" && exit 1;
fi

# Execute timestamps migration
## First run should do the migration.
bin/console itsmng:migration:timestamps --config-dir=./tests/config --ansi --no-interaction | tee $LOG_FILE
if [[ -n $(grep "No migration needed." $LOG_FILE) ]];
  then echo "bin/console glpi:migration:timestamps command FAILED" && exit 1;
fi
## Second run should do nothing.
bin/console itsmng:migration:timestamps --config-dir=./tests/config --ansi --no-interaction | tee $LOG_FILE
if [[ -z $(grep "No migration needed." $LOG_FILE) ]];
  then echo "bin/console glpi:migration:timestamps command FAILED" && exit 1;
fi

# Validate every 2.x.x baseline from 2.0.0 to latest
ITSM_2X_BASELINES=(
  "2.0.0"
  "2.0.1"
  "2.0.2"
  "2.0.3"
  "2.0.4"
  "2.0.5"
  "2.0.6"
  "2.0.7"
  "2.1.0"
  "2.1.1"
  "2.1.2"
)

for baseline in "${ITSM_2X_BASELINES[@]}"; do
  echo "Validate migration baseline: $baseline -> $CURRENT_ITSM_VERSION"
  set_baseline_version "$baseline"
  run_update --force
  if [[ -n $(grep "Unsupported version" $LOG_FILE) ]]; then
    echo "Unsupported baseline version in matrix: $baseline"
    exit 1
  fi
  assert_latest_version
  run_update_expect_no_changes
done

# Test that updated DB has same schema as newly installed DB
configure_db "glpi"
vendor/bin/atoum \
  -p 'php -d memory_limit=512M' \
  --debug \
  --force-terminal \
  --use-dot-report \
  --bootstrap-file tests/bootstrap.php \
  --no-code-coverage \
  --fail-if-skipped-methods \
  --max-children-number 1 \
  -d tests/database
