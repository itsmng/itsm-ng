<?php

namespace itsmng\Database\Runtime\Platform;

interface DatabasePlatformInterface
{
    public function getDbType(): string;

    public function getIdentifierQuoteChar(): string;

    public function normalizeOperator(string $operator): string;

    public function listTables(\itsmng\Database\Runtime\LegacyDatabase $database, $table = 'glpi\_%', array $where = []): \DBmysqlIterator;

    public function getMyIsamTables(\itsmng\Database\Runtime\LegacyDatabase $database): \DBmysqlIterator;

    public function listFields(\itsmng\Database\Runtime\LegacyDatabase $database, $table, $usecache = true);

    public function listIndexes(\itsmng\Database\Runtime\LegacyDatabase $database, $table);

    public function constraintExists(\itsmng\Database\Runtime\LegacyDatabase $database, $table, $constraint);

    public function getTableSchema(\itsmng\Database\Runtime\LegacyDatabase $database, $table, $structure = null);

    public function areTimezonesAvailable(\itsmng\Database\Runtime\LegacyDatabase $database, string &$msg = ''): bool;

    public function setTimezone(\itsmng\Database\Runtime\LegacyDatabase $database, $timezone): \itsmng\Database\Runtime\LegacyDatabase;

    public function getTimezones(\itsmng\Database\Runtime\LegacyDatabase $database): array;

    public function notTzMigrated(\itsmng\Database\Runtime\LegacyDatabase $database): int;

    public function getSignedKeysColumns(\itsmng\Database\Runtime\LegacyDatabase $database): \DBmysqlIterator;

    public function getForeignKeysContraints(\itsmng\Database\Runtime\LegacyDatabase $database): \DBmysqlIterator;

    public function getInfo(\itsmng\Database\Runtime\LegacyDatabase $database): array;

    public function getDatabaseSize(\itsmng\Database\Runtime\LegacyDatabase $database): string;

    public function getLock(\itsmng\Database\Runtime\LegacyDatabase $database, string $name): bool;

    public function releaseLock(\itsmng\Database\Runtime\LegacyDatabase $database, string $name): bool;

    public function databaseExists(\itsmng\Database\Runtime\LegacyDatabase $database, string $database_name): bool;

    public function createDatabase(\itsmng\Database\Runtime\LegacyDatabase $database, string $database_name): bool;

    public function sqlPosition(\itsmng\Database\Runtime\LegacyDatabase $database, string $needle, string $haystack): string;

    public function sqlIf(\itsmng\Database\Runtime\LegacyDatabase $database, string $condition, string $when_true, string $when_false): string;

    public function sqlGroupConcat(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, string $separator = ',', bool $distinct = false, ?string $order_by = null): string;

    public function sqlIfNull(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, string $fallback): string;

    public function sqlCastAsString(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression): string;

    public function sqlCastAsUnsignedInteger(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression): string;

    public function sqlCurrentTimestamp(\itsmng\Database\Runtime\LegacyDatabase $database): string;

    public function sqlCurrentDate(\itsmng\Database\Runtime\LegacyDatabase $database): string;

    public function sqlCurrentHour(\itsmng\Database\Runtime\LegacyDatabase $database): string;

    public function sqlLike(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, string $pattern, bool $case_sensitive = true): string;

    public function sqlFullTextBooleanMatch(\itsmng\Database\Runtime\LegacyDatabase $database, array $expressions, string $search): string;

    public function sqlFullTextBooleanScore(\itsmng\Database\Runtime\LegacyDatabase $database, array $expressions, string $search): string;

    public function sqlBitCount(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, int $width = 32): string;

    public function sqlBitwiseAnd(\itsmng\Database\Runtime\LegacyDatabase $database, string $left, string $right): string;

    public function sqlUnixTimestamp(\itsmng\Database\Runtime\LegacyDatabase $database, ?string $expression = null): string;

    public function sqlDateFormat(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, string $format): string;

    public function sqlDateTruncateToMinute(\itsmng\Database\Runtime\LegacyDatabase $database, ?string $expression = null): string;

    public function sqlDateAddInterval(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, $value, string $unit): string;

    public function sqlDateSubInterval(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression, $value, string $unit): string;

    public function sqlDateDiffDays(\itsmng\Database\Runtime\LegacyDatabase $database, string $left, string $right): string;

    public function sqlMonthDayOrdinal(\itsmng\Database\Runtime\LegacyDatabase $database, string $expression): string;

    public function sqlTimeDiffInSeconds(\itsmng\Database\Runtime\LegacyDatabase $database, string $left, string $right): string;

    public function sqlClockTimeDiffInSeconds(\itsmng\Database\Runtime\LegacyDatabase $database, string $left, string $right): string;

    public function getImplicitInsertDefaults(\itsmng\Database\Runtime\LegacyDatabase $database, string $table, array $reference): array;
}
