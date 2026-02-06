<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MatthiasMullie\Minify;

// Minify css and js files
new Itsm_minify();


class Itsm_minify
{
    /**
     * Constructor to minify css and js files
     */
    public function __construct()
    {

        $dirs = [
            'css' => [
                __DIR__ . '/../css',
                __DIR__ . '/../lib',
            ],
            'js' => [
                __DIR__ . '/../js',
                __DIR__ . '/../lib',
            ]

        ];

        $this->minify_css($dirs['css']);
        $this->minify_js($dirs['js']);
    }

    /**
     * Minify css files
     *
     * @param [type] $css : array of css directories
     * @return void
     */
    private function minify_css($css): void
    {
        foreach ($css as $css_dir) {
            if (!is_dir($css_dir)) {
                continue;
            }

            $it = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($css_dir)
                ),
                "/\\.css\$/i"
            );

            foreach ($it as $css_file) {
                // If file is not already minified
                if (!preg_match('/\.min.css$/', $css_file->getRealpath())) {
                    $minifier = new Minify\CSS($css_file->getRealpath());
                    $minifier->minify(preg_replace('/\.css$/', '.min.css', $css_file->getRealpath()));
                    echo "Minified: " . $css_file->getRealpath() . "\n";
                }
            }
        }
    }

    /**
     * Minify js files
     *
     * @param [type] $js : array of js directories
     * @return void
     */
    private function minify_js($js): void
    {
        foreach ($js as $js_dir) {
            if (!is_dir($js_dir)) {
                continue;
            }

            $it = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($js_dir)
                ),
                "/\\.js\$/i"
            );

            foreach ($it as $js_file) {
                // If file is not already minified
                if (!preg_match('/\.min.js$/', $js_file->getRealpath())) {
                    $minifier = new Minify\JS($js_file->getRealpath());
                    $minifier->minify(preg_replace('/\.js$/', '.min.js', $js_file->getRealpath()));
                    echo "Minified: " . $js_file->getRealpath() . "\n";
                }
            }
        }
    }
}
