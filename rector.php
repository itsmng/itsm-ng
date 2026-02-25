<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/inc',
        __DIR__ . '/tests/functionnal',
        __DIR__ . '/tests/units',
        __DIR__ . '/tests/imap',
        __DIR__ . '/tests/LDAP',
        __DIR__ . '/tests/web',
        __DIR__ . '/front',
    ]);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->sets([
        SetList::PHP_81,
    ]);
};
