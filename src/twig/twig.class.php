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
        global $CFG_GLPI;

        $loader = new FilesystemLoader($path);
        $twig = new Environment($loader, [
            'cache' => $cache ? './cache' : false,
            'debug' => $debug,
        ]);
        self::load_filters($twig);
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new StringExtension());

        $twig->addGlobal('root_doc', $CFG_GLPI['root_doc']);
        $twig->addGlobal('currentEntity', Session::getActiveEntity());
        $twig->addGlobal('currentRecursive', Session::getIsActiveEntityRecursive());

        return $twig;
    }

    public static function load_filters($twig)
    {
        $twig->addFilter(new TwigFilter('trans', '__'));
        $twig->addFilter(new TwigFilter('dump', function ($variable) {
            ob_start();
            dump($variable);
            $output =  ob_get_clean();
            return new \Twig\Markup($output, 'UTF-8');
        }));

        $twig->addFilter(new TwigFilter('transd', function ($string, $domain) {
            return __($string, $domain);
        }));
    }
}
