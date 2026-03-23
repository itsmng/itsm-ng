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

    public static function makeTextCriteria(string $field, string $val, bool $not = false, string $link = 'AND'): string
    {
        $db = self::db();
        $pattern = \Search::makeTextSearchValue($val);
        $like_field = $pattern !== null ? $db->sqlTextSearchExpression($field) : $field;

        if ($pattern === null) {
            $sql = $field . ' IS ' . ($not ? 'NOT ' : '') . 'NULL';
        } else {
            $sql = $not
                ? 'NOT (' . $db->sqlLike($like_field, $pattern, false) . ')'
                : $db->sqlLike($like_field, $pattern, false);
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
        return self::db()->sqlHavingReference($alias, $expression);
    }

    public static function orderByIpAddress(string $expression, string $alias): string
    {
        return self::db()->sqlOrderByIpAddress($expression, $alias);
    }

    public static function textSearchExpression(string $expression): string
    {
        return self::db()->sqlTextSearchExpression($expression);
    }

    public static function softwareLicenseNumberHavingExpression(string $qualified_table, string $field): string
    {
        $qualified = self::quoteColumn($qualified_table, $field);
        $qualified_id = self::quoteColumn($qualified_table, 'id');

        return "FLOOR(SUM($qualified) * COUNT(DISTINCT $qualified_id) / COUNT($qualified_id))";
    }
}
