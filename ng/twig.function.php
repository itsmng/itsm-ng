<?php

// Basic configuration to use TWIG


class Twig{
    public static function load($path = '../templates/', $cache = true)
    {
        $loader = new \Twig\Loader\FilesystemLoader($path);
        $twig = new \Twig\Environment($loader, [
            'cache' => $cache ? './cache' : false
        ]);
        self::load_filters($twig);
        return $twig;
    }

    public static function load_filters($twig){
        $twig->addFilter(new \Twig\TwigFilter('trans', '__'));
    }
}