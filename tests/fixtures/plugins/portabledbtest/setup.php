<?php

function plugin_version_portabledbtest()
{
    return [
        'name'         => 'Portable DB test plugin',
        'version'      => '1.0.0',
        'author'       => 'GLPI Test suite',
        'license'      => 'GPL v2+',
        'requirements' => [
            'glpi' => [
                'min' => '10.0.0',
            ],
        ],
    ];
}

function plugin_init_portabledbtest()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['portabledbtest'] = true;
    $PLUGIN_HOOKS['init_session']['portabledbtest'] = 'plugin_portabledbtest_init_session';
    $PLUGIN_HOOKS['change_profile']['portabledbtest'] = 'plugin_portabledbtest_change_profile';
    $PLUGIN_HOOKS['portabledbtest_standard_hook']['portabledbtest'] = 'plugin_portabledbtest_handle_standard_hook';
    $PLUGIN_HOOKS['portabledbtest_object_hook']['portabledbtest']['Computer'] = 'plugin_portabledbtest_handle_object_hook';
    $PLUGIN_HOOKS['portabledbtest_reduce_hook']['portabledbtest'] = 'plugin_portabledbtest_handle_reduce_hook';
}
