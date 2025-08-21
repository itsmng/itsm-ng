<?php

namespace Itsmng\Helper;

use Doctrine\ORM\QueryBuilder;

class FullTextHelper
{
    public static function applyFullText(QueryBuilder $qb, string $alias, array $fields, string $search, string $lang = 'french'): void
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'pdo_mysql';

        // Normalize fields: if element already contains a dot, keep it, else prefix with alias
        $fieldExprs = array_map(function ($f) use ($alias) {
            return strpos($f, '.') !== false ? $f : ($alias . '.' . $f);
        }, $fields);

        if ($driver === 'pdo_pgsql') {
            // PostgreSQL: construct a concat of fields and use DQL custom functions
            $concat = 'CONCAT(' . implode(", ' ', ", $fieldExprs) . ')';

            $tsvector = "TO_TSVECTOR('" . $lang . "', " . $concat . ")";
            $tsquery  = "PLAINTO_TSQUERY('" . $lang . "', :search)";

            // TS_MATCH and TS_RANK must be registered as DQL functions that emit the proper SQL
                // compare to TRUE so DQL parser recognizes a conditional/comparison expression
                $qb->andWhere("TS_MATCH($tsvector, $tsquery) = TRUE")
                    ->addSelect("TS_RANK($tsvector, $tsquery) AS HIDDEN score")
                    ->orderBy('score', 'DESC');
    } else {
            // MySQL fulltext (MATCH ... AGAINST)
            $matchFieldsStr = implode(',', $fieldExprs);

                // in MySQL boolean fulltext, MATCH...AGAINST returns a relevance score (0 = no match)
                $qb->andWhere("MATCH($matchFieldsStr) AGAINST (:search IN BOOLEAN MODE) > 0")
                    ->addSelect("MATCH($matchFieldsStr) AGAINST (:search IN BOOLEAN MODE) AS HIDDEN score")
                    ->orderBy('score', 'DESC');
        }

        $qb->setParameter('search', $search);
    }
}