<?php

namespace itsmng\Database\Runtime;

use QueryExpression;
use QueryParam;
use itsmng\Database\Runtime\Platform\PlatformResolver;

class LegacySqlQuoter
{
    public static function quoteName($name, ?string $dbtype = null): string
    {
        $quote = PlatformResolver::resolve($dbtype ?? self::resolveCurrentDbType())->getIdentifierQuoteChar();

        if ($name instanceof QueryExpression) {
            return $name->getValue();
        }

        $name_matches = preg_split('/\s+AS\s+/i', (string) $name, 2);
        if (is_array($name_matches) && count($name_matches) === 2) {
            return self::quoteName($name_matches[0], $dbtype) . ' AS ' . self::quoteName($name_matches[1], $dbtype);
        }

        if (strpos((string) $name, '.')) {
            $names = explode('.', (string) $name);
            return implode('.', array_map(fn ($chunk) => self::quoteName($chunk, $dbtype), $names));
        }

        if ($name === '*') {
            return $name;
        }

        foreach (['`', '"'] as $already_quote) {
            if (
                str_starts_with((string) $name, $already_quote)
                && str_ends_with((string) $name, $already_quote)
            ) {
                $name = trim((string) $name, $already_quote);
                break;
            }
        }

        return sprintf(
            '%1$s%2$s%1$s',
            $quote,
            str_replace($quote, $quote . $quote, (string) $name)
        );
    }

    public static function quoteValue($value, ?callable $compatible_value_quoter = null)
    {
        if ($compatible_value_quoter !== null) {
            return $compatible_value_quoter($value);
        }

        if ($value instanceof QueryParam || $value instanceof QueryExpression) {
            return $value->getValue();
        }

        if ($value === null || $value === 'NULL' || $value === 'null') {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return "'" . (string) $value . "'";
    }

    public static function isNameQuoted($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        foreach (['`', '"'] as $quote) {
            if (trim($value, $quote) != $value) {
                return true;
            }
        }

        return false;
    }

    private static function resolveCurrentDbType(): string
    {
        $database = $GLOBALS['DB'] ?? null;

        if ($database instanceof LegacyDatabase && !empty($database->dbtype)) {
            return $database->dbtype;
        }

        return 'mysql';
    }
}
