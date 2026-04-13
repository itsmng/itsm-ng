<?php

namespace itsmng\Database\Runtime\Platform;

interface DatabasePlatformInterface
{
    public function getDbType(): string;

    public function getIdentifierQuoteChar(): string;

    public function normalizeOperator(string $operator): string;

    public function listTables($table = 'glpi\_%', array $where = []): \DBmysqlIterator;

    public function listFields($table, $usecache = true);

    public function listIndexes($table);

    public function constraintExists($table, $constraint);

    public function getTableSchema($table, $structure = null);

    public function areTimezonesAvailable(string &$msg = ''): bool;

    public function setTimezone($timezone): \itsmng\Database\Runtime\LegacyDatabase;

    public function getTimezones(): array;

    public function notTzMigrated(): int;

    public function getSignedKeysColumns(): \DBmysqlIterator;

    public function getForeignKeysContraints(): \DBmysqlIterator;

    public function getInfo(): array;

    public function getDatabaseSize(): string;

    public function getLock(string $name): bool;

    public function releaseLock(string $name): bool;

    public function databaseExists(string $database_name): bool;

    public function createDatabase(string $database_name): bool;

    public function sqlPosition(string $needle, string $haystack): string;

    public function sqlIf(string $condition, string $when_true, string $when_false): string;

    public function sqlGroupConcat(string $expression, string $separator = ',', bool $distinct = false, ?string $order_by = null): string;

    public function sqlIfNull(string $expression, string $fallback): string;

    public function sqlCastAsString(string $expression): string;

    public function sqlConcat(array $expressions): string;

    public function sqlCastAsUnsignedInteger(string $expression): string;

    public function sqlCurrentTimestamp(): string;

    public function sqlCurrentDate(): string;

    public function sqlCurrentHour(): string;

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string;

    public function sqlFullTextBooleanMatch(array $expressions, string $search): string;

    public function sqlFullTextBooleanScore(array $expressions, string $search): string;

    public function sqlBitCount(string $expression, int $width = 32): string;

    public function sqlBitwiseAnd(string $left, string $right): string;

    public function sqlUnixTimestamp(?string $expression = null): string;

    public function sqlDateFormat(string $expression, string $format): string;

    public function sqlDateTruncateToMinute(?string $expression = null): string;

    public function sqlDateAddInterval(string $expression, $value, string $unit): string;

    public function sqlDateSubInterval(string $expression, $value, string $unit): string;

    public function sqlDateDiffDays(string $left, string $right): string;

    public function sqlMonthDayOrdinal(string $expression): string;

    public function sqlTimeDiffInSeconds(string $left, string $right): string;

    public function sqlClockTimeDiffInSeconds(string $left, string $right): string;

    public function getImplicitInsertDefaults(string $table, array $reference): array;
}
