<?php

namespace itsmng\Search\Provider;

use DBmysql;

final class LegacySQLProvider
{
    private static function db(): DBmysql
    {
        global $DB;

        return $DB;
    }

    private static function quoteColumn(string $table, string $field): string
    {
        return self::db()->quoteName($table . '.' . $field);
    }

    private static function isPgsql(): bool
    {
        return self::db()->dbtype === 'pgsql';
    }

    public static function makeTextCriteria(string $field, string $val, bool $not = false, string $link = 'AND'): string
    {
        $pattern = \Search::makeTextSearchValue($val);
        $like_field = $pattern !== null ? self::textSearchExpression($field) : $field;

        if ($pattern === null) {
            $sql = $field . ' IS ' . ($not ? 'NOT ' : '') . 'NULL';
        } else {
            $sql = $not
                ? 'NOT (' . self::db()->sqlLike($like_field, $pattern, false) . ')'
                : self::db()->sqlLike($like_field, $pattern, false);
        }

        $sql_or = '';
        if (strtolower($val) === 'null') {
            $sql_or = "OR $field = ''";
        }

        if (
            ($not && ($val !== 'NULL') && ($val !== 'null') && ($val !== '^$'))
            || (!$not && ($val === '^$'))
        ) {
            $sql = "($sql OR $field IS NULL)";
        }

        return " $link ($sql $sql_or)";
    }

    public static function havingReference(string $alias, string $expression): string
    {
        return self::isPgsql()
            ? $expression
            : self::db()->quoteName($alias);
    }

    public static function orderByIpAddress(string $expression, string $alias): string
    {
        return self::isPgsql()
            ? self::db()->quoteName($alias)
            : self::castIpAddress($expression);
    }

    public static function textSearchExpression(string $expression): string
    {
        return self::isPgsql()
            ? self::db()->sqlCastAsString($expression)
            : $expression;
    }

    public static function castIpAddress(string $expression): string
    {
        return self::isPgsql()
            ? 'CAST(' . $expression . ' AS inet)'
            : 'INET_ATON(' . $expression . ')';
    }

    public static function softwareLicenseNumberHavingExpression(string $qualified_table, string $field): string
    {
        $qualified = self::quoteColumn($qualified_table, $field);
        $qualified_id = self::quoteColumn($qualified_table, 'id');

        return "FLOOR(SUM($qualified) * COUNT(DISTINCT $qualified_id) / COUNT($qualified_id))";
    }
}
