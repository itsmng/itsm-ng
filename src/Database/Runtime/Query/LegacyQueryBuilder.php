<?php

namespace itsmng\Database\Runtime\Query;

use AbstractQuery;
use Countable;
use InvalidArgumentException;
use Iterator;
use QueryExpression;
use QueryParam;
use QuerySubQuery;
use RuntimeException;
use Toolbox;
use itsmng\Database\Runtime\LegacyDatabase;
use itsmng\Database\Runtime\LegacySqlQuoter;
use itsmng\Database\Runtime\Platform\PlatformResolver;

class LegacyQueryBuilder
{
    private ?LegacyDatabase $conn;

    private ?string $sql = null;

    private array $allowed_operators = [
        '=',
        '!=',
        '<',
        '<=',
        '>',
        '>=',
        '<>',
        'LIKE',
        'REGEXP',
        'NOT REGEXP',
        'NOT LIKE',
        'NOT REGEX',
        '&',
        '|',
    ];

    public function __construct(?LegacyDatabase $dbconnection)
    {
        $this->conn = $dbconnection;
    }

    public function buildQuery($table, $crit = "", $log = false): string
    {
        $this->sql = null;

        $is_legacy = false;
        if (is_string($table) && strpos($table, " ")) {
            $names = preg_split('/\s+AS\s+/i', $table);
            if (isset($names[1]) && strpos($names[1], ' ') || !isset($names[1]) || strpos($names[0], ' ')) {
                $is_legacy = true;
            }
        }

        if ($is_legacy) {
            $this->sql = $table;
        } else {
            if (is_array($table) && isset($table['FROM'])) {
                $crit = $table;
                $table = $crit['FROM'];
                unset($crit['FROM']);
            }

            $field = "";
            $distinct = false;
            $orderby = null;
            $limit = 0;
            $start = 0;
            $where = '';
            $count = '';
            $join = [];
            $groupby = '';
            $having = '';

            if (is_array($crit) && count($crit)) {
                foreach ($crit as $key => $val) {
                    switch ((string) $key) {
                        case 'SELECT':
                        case 'FIELDS':
                            $field = $val;
                            unset($crit[$key]);
                            break;
                        case 'DISTINCT':
                            if ($val) {
                                $distinct = true;
                            }
                            unset($crit[$key]);
                            break;
                        case 'COUNT':
                            $count = $val;
                            unset($crit[$key]);
                            break;
                        case 'ORDER':
                        case 'ORDERBY':
                            $orderby = $val;
                            unset($crit[$key]);
                            break;
                        case 'LIMIT':
                            $limit = $val;
                            unset($crit[$key]);
                            break;
                        case 'START':
                            $start = $val;
                            unset($crit[$key]);
                            break;
                        case 'WHERE':
                            $where = $val;
                            unset($crit[$key]);
                            break;
                        case 'HAVING':
                            $having = $val;
                            unset($crit[$key]);
                            break;
                        case 'GROUP':
                        case 'GROUPBY':
                            $groupby = $val;
                            unset($crit[$key]);
                            break;
                        case 'JOIN':
                        case 'LEFT JOIN':
                        case 'RIGHT JOIN':
                        case 'INNER JOIN':
                            $join[$key] = $val;
                            unset($crit[$key]);
                            break;
                    }
                }
            }

            $this->sql = 'SELECT ';
            $first = true;

            if ($count) {
                $this->sql .= 'COUNT(';
                if ($distinct) {
                    $this->sql .= 'DISTINCT ';
                }
                if (!empty($field) && !is_array($field)) {
                    $this->sql .= $this->quoteName($field);
                } else {
                    if ($distinct) {
                        throw new InvalidArgumentException("With COUNT and DISTINCT, you must specify exactly one field, or use 'COUNT DISTINCT'");
                    }
                    $this->sql .= "*";
                }
                $this->sql .= ") AS $count";
                $first = false;
            }

            if (!$count || ($count && is_array($field) && !empty($groupby))) {
                if ($distinct && !$count) {
                    $this->sql .= 'DISTINCT ';
                }
                if (empty($field)) {
                    $this->sql .= '*';
                }
                if (!empty($field)) {
                    if (!is_array($field)) {
                        $field = [$field];
                    }
                    foreach ($field as $t => $f) {
                        if ($first) {
                            $first = false;
                        } else {
                            $this->sql .= ', ';
                        }
                        $this->sql .= $this->handleFields($t, $f);
                    }
                }
            }

            if (is_array($table)) {
                if (count($table)) {
                    $table = array_map(fn ($name) => $this->quoteName($name), $table);
                    $this->sql .= ' FROM ' . implode(", ", $table);
                } else {
                    throw new InvalidArgumentException("Missing table name");
                }
            } elseif ($table) {
                if ($table instanceof AbstractQuery) {
                    $table = $table->getQuery();
                } elseif ($table instanceof QueryExpression) {
                    $table = $table->getValue();
                } else {
                    $table = $this->quoteName($table);
                }
                $this->sql .= " FROM $table";
            } else {
                throw new InvalidArgumentException("Missing table name");
            }

            if (!empty($join)) {
                $this->sql .= $this->analyseJoins($join);
            }

            if (!empty($crit)) {
                $this->sql .= " WHERE " . $this->analyseCrit($crit);
                if ($where) {
                    trigger_error(
                        'Criteria found both inside and outside "WHERE" key. Some of them will be ignored',
                        E_USER_WARNING
                    );
                }
            } elseif ($where) {
                $this->sql .= " WHERE " . $this->analyseCrit($where);
            }

            if (is_array($groupby)) {
                if (count($groupby)) {
                    $groupby = array_map(fn ($name) => $this->quoteName($name), $groupby);
                    $this->sql .= ' GROUP BY ' . implode(", ", $groupby);
                } else {
                    throw new InvalidArgumentException("Missing group by field");
                }
            } elseif ($groupby) {
                $groupby = $this->quoteName($groupby);
                $this->sql .= " GROUP BY $groupby";
            }

            if ($having) {
                $this->sql .= " HAVING " . $this->analyseCrit($having);
            }

            if ($orderby !== null && !$count) {
                $this->sql .= $this->handleOrderClause($orderby);
            }

            $this->sql .= $this->handleLimits($limit, $start);
        }

        if ($log == true || defined('GLPI_SQL_DEBUG') && GLPI_SQL_DEBUG == true) {
            Toolbox::logSqlDebug("Generated query:", $this->getSql());
        }

        return $this->getSql();
    }

    public function handleOrderClause($clause): string
    {
        if (!is_array($clause)) {
            $clause = [$clause];
        }

        $cleanorderby = [];
        foreach ($clause as $o) {
            if (is_string($o)) {
                $fields = explode(',', $o);
                foreach ($fields as $field) {
                    $new = '';
                    $tmp = explode(' ', trim($field));
                    $new .= $this->quoteName($tmp[0]);
                    if (isset($tmp[1]) && in_array($tmp[1], ['ASC', 'DESC'])) {
                        $new .= ' ' . $tmp[1];
                    }
                    $cleanorderby[] = $new;
                }
            } elseif ($o instanceof QueryExpression) {
                $cleanorderby[] = $o->getValue();
            } else {
                throw new InvalidArgumentException("Invalid order clause");
            }
        }

        return " ORDER BY " . implode(", ", $cleanorderby);
    }

    public function handleLimits($limit, $offset = null): string
    {
        $limits = '';
        if (is_numeric($limit) && ($limit > 0)) {
            $limits = " LIMIT $limit";
            if (is_numeric($offset) && ($offset > 0)) {
                $limits .= " OFFSET $offset";
            }
        }
        return $limits;
    }

    public function getSql(): string
    {
        return trim((string) $this->sql);
    }

    public function analyseCrit($crit, $bool = "AND"): string
    {
        if (!is_array($crit)) {
            if (is_bool($crit)) {
                return $crit ? 'TRUE' : 'FALSE';
            }
            if (in_array($crit, [0, 1, '0', '1'], true)) {
                return ((int) $crit) === 1 ? '1 = 1' : '1 = 0';
            }
            return $crit;
        }

        $ret = "";
        foreach ($crit as $name => $value) {
            if (!empty($ret)) {
                $ret .= " $bool ";
            }
            if (is_numeric($name)) {
                if ($value instanceof QueryExpression) {
                    $ret .= $value->getValue();
                } elseif ($value instanceof QuerySubQuery) {
                    $ret .= $value->getQuery();
                } elseif (is_bool($value)) {
                    $ret .= $value ? 'TRUE' : 'FALSE';
                } elseif (in_array($value, [0, 1, '0', '1'], true)) {
                    $ret .= ((int) $value) === 1 ? '1 = 1' : '1 = 0';
                } else {
                    $ret .= "(" . $this->analyseCrit($value) . ")";
                }
            } elseif (($name === "OR") || ($name === "AND")) {
                $ret .= "(" . $this->analyseCrit($value, $name) . ")";
            } elseif ($name === "NOT") {
                $ret .= "NOT (" . $this->analyseCrit($value) . ")";
            } elseif ($name === "FKEY" || $name === 'ON') {
                $ret .= $this->analyseFkey($value);
            } elseif ($name === 'RAW') {
                $key = key($value);
                $value = current($value);
                $ret .= '((' . $key . ') ' . $this->analyseCriterion($value) . ')';
            } else {
                $quoted_name = $this->quoteName($name);

                if (
                    $this->conn instanceof LegacyDatabase
                    && $this->conn->dbtype === 'pgsql'
                    && is_array($value)
                    && count($value) === 2
                    && isset($value[0])
                    && $this->isOperator($value[0])
                    && in_array($this->normalizeOperator($value[0]), ['&', '|'], true)
                ) {
                    $ret .= '(' . $quoted_name . ' ' . $this->analyseCriterion($value, (string) $name);
                } else {
                    $ret .= $quoted_name . ' ' . $this->analyseCriterion($value, (string) $name);
                }
            }
        }

        return $ret;
    }

    public function analyseJoins(array $joinarray): string
    {
        $query = '';
        foreach ($joinarray as $jointype => $jointables) {
            if (!in_array($jointype, ['JOIN', 'LEFT JOIN', 'INNER JOIN', 'RIGHT JOIN'])) {
                throw new RuntimeException('BAD JOIN');
            }

            if ($jointype == 'JOIN') {
                $jointype = 'LEFT JOIN';
            }

            if (!is_array($jointables)) {
                throw new InvalidArgumentException("BAD JOIN, value must be [ table => criteria ]");
            }

            foreach ($jointables as $jointablekey => $jointablecrit) {
                if (isset($jointablecrit['TABLE'])) {
                    $jointablekey = $jointablecrit['TABLE'];
                    unset($jointablecrit['TABLE']);
                } elseif (is_numeric($jointablekey) || $jointablekey == 'FKEY' || $jointablekey == 'ON') {
                    throw new RuntimeException('BAD JOIN');
                }

                if ($jointablekey instanceof QuerySubQuery) {
                    $jointablekey = $jointablekey->getQuery();
                } else {
                    $jointablekey = $this->quoteName($jointablekey);
                }

                $query .= " $jointype $jointablekey ON (" . $this->analyseCrit($jointablecrit) . ")";
            }
        }

        return $query;
    }

    public function isOperator($value): bool
    {
        return in_array($value, $this->allowed_operators, true);
    }

    private function handleFields($t, $f): string
    {
        if (is_numeric($t)) {
            if ($f instanceof AbstractQuery) {
                return $f->getQuery();
            } elseif ($f instanceof QueryExpression) {
                return $f->getValue();
            }

            return $this->quoteName($f);
        }

        switch ($t) {
            case 'COUNT DISTINCT':
            case 'DISTINCT COUNT':
                if (is_array($f)) {
                    $sub_count = [];
                    foreach ($f as $sub_f) {
                        $sub_count[] = $this->handleFieldsAlias("COUNT(DISTINCT", $sub_f, ')');
                    }
                    return implode(", ", $sub_count);
                }
                return $this->handleFieldsAlias("COUNT(DISTINCT", $f, ')');
            case 'COUNT':
            case 'SUM':
            case 'AVG':
            case 'MAX':
            case 'MIN':
                if (is_array($f)) {
                    $sub_aggr = [];
                    foreach ($f as $sub_f) {
                        $sub_aggr[] = $this->handleFields($t, $sub_f);
                    }
                    return implode(", ", $sub_aggr);
                }
                return $this->handleFieldsAlias($t, $f);
            default:
                if (is_array($f)) {
                    $t = $this->quoteName($t);
                    $f = array_map(fn ($name) => $this->quoteName($name), $f);
                    return "$t." . implode(", $t.", $f);
                }
                $t = $this->quoteName($t);
                $f = ($f == '*' ? $f : $this->quoteName($f));
                return "$t.$f";
        }
    }

    private function handleFieldsAlias($t, $f, $suffix = ''): string
    {
        $names = preg_split('/\s+AS\s+/i', $f);
        $expr  = "$t(" . $this->handleFields(0, $names[0]) . "$suffix)";
        if (isset($names[1])) {
            $expr .= " AS " . $this->quoteName($names[1]);
        }

        return $expr;
    }

    private function analyseCriterion($value, ?string $field_name = null): string
    {
        if (is_null($value) || (is_string($value) && strtolower($value) === 'null')) {
            return 'IS NULL';
        }

        if (is_array($value)) {
            if (count($value) == 2 && isset($value[0]) && $this->isOperator($value[0])) {
                $comparison = $this->normalizeOperator($value[0]);
                $criterion_value = $value[1];
            } else {
                if (!count($value)) {
                    throw new RuntimeException('Empty IN are not allowed');
                }
                return "IN (" . $this->analyseCriterionValue($value, $field_name) . ")";
            }
        } else {
            $comparison = ($value instanceof AbstractQuery ? 'IN' : '=');
            $criterion_value = $value;
        }

        if (
            $this->conn instanceof LegacyDatabase
            && $this->conn->dbtype === 'pgsql'
            && in_array($comparison, ['&', '|'], true)
        ) {
            return $comparison . ' ' . $this->getCriterionValue($criterion_value, $field_name) . ') <> 0';
        }

        $criterion = "$comparison " . $this->getCriterionValue($criterion_value, $field_name);
        if (
            $this->conn instanceof LegacyDatabase
            && $this->conn->dbtype === 'pgsql'
            && in_array($comparison, ['LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE'], true)
        ) {
            $criterion .= " ESCAPE E'\\\\'";
        }

        return $criterion;
    }

    private function getCriterionValue($value, ?string $field_name = null): string
    {
        if ($value instanceof AbstractQuery) {
            return $value->getQuery();
        } elseif ($value instanceof QueryExpression) {
            return $value->getValue();
        } elseif ($value instanceof QueryParam) {
            return $value->getValue();
        }

        return $this->analyseCriterionValue($value, $field_name);
    }

    private function analyseCriterionValue($value, ?string $field_name = null): string
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->quoteCriterionValue($v, $field_name);
            }
            return implode(', ', $value);
        }

        return $this->quoteCriterionValue($value, $field_name);
    }

    private function analyseFkey($values): string
    {
        if (is_array($values)) {
            $keys = array_keys($values);
            if (count($values) == 2) {
                $t1 = $keys[0];
                $f1 = $values[$t1];
                $t2 = $keys[1];
                $f2 = $values[$t2];
                if ($f2 instanceof QuerySubQuery) {
                    return (is_numeric($t1) ? $this->quoteName($f1) : $this->quoteName($t1) . '.' . $this->quoteName($f1)) . ' = '
                        . $f2->getQuery();
                }

                return (is_numeric($t1) ? $this->quoteName($f1) : $this->quoteName($t1) . '.' . $this->quoteName($f1)) . ' = '
                    . (is_numeric($t2) ? $this->quoteName($f2) : $this->quoteName($t2) . '.' . $this->quoteName($f2));
            } elseif (count($values) == 3) {
                $condition = array_pop($values);
                $fkey = $this->analyseFkey($values);
                return $fkey . ' ' . key($condition) . ' ' . $this->analyseCrit(current($condition));
            }
        }

        throw new InvalidArgumentException("BAD FOREIGN KEY, should be [ table1 => key1, table2 => key2 ] or [ table1 => key1, table2 => key2, [criteria]]");
    }

    private function normalizeOperator(string $operator): string
    {
        return PlatformResolver::resolve($this->conn?->dbtype)->normalizeOperator($operator);
    }

    private function quoteName($name): string
    {
        return LegacySqlQuoter::quoteName($name, $this->conn?->dbtype);
    }

    private function quoteCriterionValue($value, ?string $field_name = null): string
    {
        if ($this->conn instanceof LegacyDatabase && ($this->conn->connected ?? false)) {
            return $this->conn->quoteFieldValue(null, $field_name ?? '', $value);
        }

        return LegacySqlQuoter::quoteValue(
            $value,
            $this->conn instanceof LegacyDatabase && ($this->conn->connected ?? false)
                ? $this->conn->quoteCompatibleValue(...)
                : null
        );
    }
}
