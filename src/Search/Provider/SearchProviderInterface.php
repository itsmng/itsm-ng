<?php

namespace itsmng\Search\Provider;

interface SearchProviderInterface
{
    public static function constructSQL(array &$data);

    public static function constructData(array &$data, $onlycount = false);

    public static function addHaving($LINK, $NOT, $itemtype, $ID, $searchtype, $val, bool $meta = false, ?string $meta_type = null);

    public static function addOrderBy($itemtype, $ID, $order);

    public static function addDefaultSelect($itemtype, array &$groupby_fields = []);

    public static function addSelect(
        $itemtype,
        $ID,
        $meta = 0,
        $meta_type = 0,
        array &$groupby_fields = [],
        ?array $searchopt_override = null
    );

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
    );

    public static function makeTextCriteria($field, $val, $not = false, $link = 'AND');
}
