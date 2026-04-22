<?php

function plugin_version_legacydbtest()
{
    return [
        'name'         => 'Legacy DB test plugin',
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

function plugin_init_legacydbtest()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['legacydbtest'] = true;
    $PLUGIN_HOOKS['init_session']['legacydbtest'] = 'plugin_legacydbtest_init_session';
    $PLUGIN_HOOKS['change_profile']['legacydbtest'] = 'plugin_legacydbtest_change_profile';
    $PLUGIN_HOOKS['legacydbtest_standard_hook']['legacydbtest'] = 'plugin_legacydbtest_handle_standard_hook';
    $PLUGIN_HOOKS['legacydbtest_object_hook']['legacydbtest']['Computer'] = 'plugin_legacydbtest_handle_object_hook';
    $PLUGIN_HOOKS['legacydbtest_reduce_hook']['legacydbtest'] = 'plugin_legacydbtest_handle_reduce_hook';
}
