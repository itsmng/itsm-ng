<?php

namespace itsmng\Database\Schema;

final class CoreSchemaSupplement
{
    public static function tables(): array
    {
        return [
            [
                'name' => 'glpi_devicesimcards',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'designation',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'manufacturers_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'voltage',
                        'type' => 'int32',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'devicesimcardtypes_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'allow_voip',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'designation',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'designation',
                            ],
                        ],
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'entities_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'is_recursive',
                            ],
                        ],
                    ],
                    [
                        'name' => 'devicesimcardtypes_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'devicesimcardtypes_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_mod',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_creation',
                            ],
                        ],
                    ],
                    [
                        'name' => 'manufacturers_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'manufacturers_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_items_devicesimcards',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'items_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'RELATION to various table, according to itemtype (id)',
                    ],
                    [
                        'name' => 'itemtype',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 100,
                    ],
                    [
                        'name' => 'devicesimcards_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_deleted',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_dynamic',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'serial',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'otherserial',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'states_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'locations_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'lines_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'users_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'groups_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'pin',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'pin2',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'puk',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'puk2',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'msin',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'item',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'itemtype',
                            ],
                            [
                                'name' => 'items_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'devicesimcards_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'devicesimcards_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'is_deleted',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'is_deleted',
                            ],
                        ],
                    ],
                    [
                        'name' => 'is_dynamic',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'is_dynamic',
                            ],
                        ],
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'entities_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'is_recursive',
                            ],
                        ],
                    ],
                    [
                        'name' => 'serial',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'serial',
                            ],
                        ],
                    ],
                    [
                        'name' => 'otherserial',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'otherserial',
                            ],
                        ],
                    ],
                    [
                        'name' => 'states_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'states_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'locations_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'locations_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'lines_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'lines_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'users_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'users_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'groups_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'groups_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_devicesimcardtypes',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'name',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_mod',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_creation',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_lineoperators',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'mcc',
                        'type' => 'int32',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'mnc',
                        'type' => 'int32',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'name',
                            ],
                        ],
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'entities_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'is_recursive',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_mod',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_creation',
                            ],
                        ],
                    ],
                    [
                        'name' => 'unicity',
                        'type' => 'unique',
                        'columns' => [
                            [
                                'name' => 'mcc',
                            ],
                            [
                                'name' => 'mnc',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_linetypes',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'name',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_mod',
                            ],
                        ],
                    ],
                    [
                        'name' => 'date_creation',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'date_creation',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_appliances_items',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'appliances_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'items_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'itemtype',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 100,
                        'default' => '',
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'unicity',
                        'type' => 'unique',
                        'columns' => [
                            [
                                'name' => 'appliances_id',
                            ],
                            [
                                'name' => 'items_id',
                            ],
                            [
                                'name' => 'itemtype',
                            ],
                        ],
                    ],
                    [
                        'name' => 'appliances_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'appliances_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'item',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'itemtype',
                            ],
                            [
                                'name' => 'items_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_appliancetypes',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_recursive',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                    [
                        'name' => 'externalidentifier',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'name',
                            ],
                        ],
                    ],
                    [
                        'name' => 'entities_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'entities_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'unique_externalidentifier',
                        'type' => 'unique',
                        'columns' => [
                            [
                                'name' => 'externalidentifier',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_applianceenvironments',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'text',
                        'nullable' => true,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'name',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'name',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_appliances_items_relations',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'appliances_items_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'itemtype',
                        'type' => 'string',
                        'nullable' => false,
                        'length' => 100,
                    ],
                    [
                        'name' => 'items_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'appliances_items_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'appliances_items_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'itemtype',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'itemtype',
                            ],
                        ],
                    ],
                    [
                        'name' => 'items_id',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'items_id',
                            ],
                        ],
                    ],
                    [
                        'name' => 'item',
                        'type' => 'index',
                        'columns' => [
                            [
                                'name' => 'itemtype',
                            ],
                            [
                                'name' => 'items_id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_oidc_config',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'Provider',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'ClientID',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'ClientSecret',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'is_activate',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'is_forced',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'scope',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'proxy',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'cert',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'sso_link_users',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '1',
                    ],
                    [
                        'name' => 'logout',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_specialstatuses',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                    [
                        'name' => 'weight',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '1',
                    ],
                    [
                        'name' => 'is_active',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '1',
                    ],
                    [
                        'name' => 'color',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_oidc_mapping',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'given_name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'family_name',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'picture',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'email',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'locale',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'phone_number',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'group',
                        'type' => 'string',
                        'nullable' => true,
                        'length' => 255,
                        'default' => '',
                    ],
                    [
                        'name' => 'date_mod',
                        'type' => 'timestamp',
                        'nullable' => true,
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'glpi_oidc_users',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => 'int32',
                        'nullable' => false,
                        'autoIncrement' => true,
                    ],
                    [
                        'name' => 'user_id',
                        'type' => 'int32',
                        'nullable' => false,
                        'default' => '0',
                    ],
                    [
                        'name' => 'update',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => '0',
                    ],
                ],
                'indexes' => [
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [
                            [
                                'name' => 'id',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
