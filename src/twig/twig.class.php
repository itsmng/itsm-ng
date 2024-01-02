<?php

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

// Basic configuration to use TWIG

class Twig
{
    public static function load($path = '../templates/', $cache = true, $debug = false)
    {
        $loader = new FilesystemLoader($path);
        $twig = new Environment($loader, [
            'cache' => $cache ? './cache' : false,
            'debug' => $debug,
        ]);
        self::load_filters($twig);
        $twig->addExtension(new DebugExtension);
        $twig->addExtension(new StringExtension);

        return $twig;
    }

    public static function load_filters($twig)
    {
        $twig->addFilter(new TwigFilter('trans', '__'));
    }
}
