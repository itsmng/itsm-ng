#!/bin/bash -e

ATOUM_ADDITIONNAL_OPTIONS=""
if [[ "$CODE_COVERAGE" = true ]]; then
  export COVERAGE_DIR="coverage-unit"
else
  ATOUM_ADDITIONNAL_OPTIONS="--no-code-coverage";
fi

# Unit test
vendor/bin/atoum \
  -p 'php -d memory_limit=512M' \
  --debug \
  --force-terminal \
  --use-dot-report \
  --bootstrap-file tests/bootstrap.php \
  --fail-if-skipped-methods \
  $ATOUM_ADDITIONNAL_OPTIONS \
  --max-children-number 10 \
  -d tests/units

unset COVERAGE_DIR
