<?php
/**
 * 
 * ITSM-NG
 *  
 */
define('ITSM_ROOT', realpath('../..'));
class Language {

    public static $languages = [];
    private static $languages_json_path = "./languages.json";



    /**
    * Get available languages
    *
    * @since ITSM 2.0.0
    *
    * @return array
    */
    public static function getLanguages(): array{

    foreach (json_decode(file_get_contents(self::$languages_json_path), true) as $code => $language) {
        if (isset($language['file']) && is_file(ITSM_ROOT . "/locales/" . $language['file'])) {
            $languages[$code] = $language['native'];
        }
    }
    return $languages;
    }

}