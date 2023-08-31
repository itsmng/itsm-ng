<?php

// Basic configuration to use TWIG


class Twig{
    public static function load($path = '../templates/', $cache = true, $debug = false)
    {
        $loader = new \Twig\Loader\FilesystemLoader($path);
        $twig = new \Twig\Environment($loader, [
            'cache' => $cache ? './cache' : false,
            'debug' => true
        ]);
        self::load_filters($twig);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        return $twig;
    }

    public static function load_filters($twig){
        $twig->addFilter(new \Twig\TwigFilter('trans', '__'));
    }
}