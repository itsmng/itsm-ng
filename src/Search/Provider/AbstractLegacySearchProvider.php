<?php

namespace itsmng\Search\Provider;

abstract class AbstractLegacySearchProvider implements SearchProviderInterface
{
    public static function constructSQL(array &$data)
    {
        \Search::constructSQLInternal($data);
    }

    public static function constructData(array &$data, $onlycount = false)
    {
        \Search::constructDataInternal($data, $onlycount);
    }

    public static function addHaving($LINK, $NOT, $itemtype, $ID, $searchtype, $val, bool $meta = false, ?string $meta_type = null)
    {
        return \Search::addHavingInternal($LINK, $NOT, $itemtype, $ID, $searchtype, $val, $meta, $meta_type);
    }

    public static function addOrderBy($itemtype, $ID, $order)
    {
        return \Search::addOrderByInternal($itemtype, $ID, $order);
    }

    public static function addDefaultSelect($itemtype, array &$groupby_fields = [])
    {
        return \Search::addDefaultSelectInternal($itemtype, $groupby_fields);
    }

    public static function addSelect(
        $itemtype,
        $ID,
        $meta = 0,
        $meta_type = 0,
        array &$groupby_fields = [],
        ?array $searchopt_override = null
    ) {
        return \Search::addSelectInternal(
            $itemtype,
            $ID,
            $meta,
            $meta_type,
            $groupby_fields,
            $searchopt_override
        );
    }

    public static function addLeftJoin(
        $itemtype,
        $ref_table,
        array &$already_link_tables,
        $new_table,
        $linkfield,
        $meta = 0,
        $meta_type = 0,
        $joinparams = [],
        $field = ''
    ) {
        return \Search::addLeftJoinInternal(
            $itemtype,
            $ref_table,
            $already_link_tables,
            $new_table,
            $linkfield,
            $meta,
            $meta_type,
            $joinparams,
            $field
        );
    }

    public static function makeTextCriteria($field, $val, $not = false, $link = 'AND')
    {
        return \Search::makeTextCriteriaInternal($field, $val, $not, $link);
    }
}
