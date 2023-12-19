<?php
/**
 * 
 * ITSM-NG
 *  
 */

class Language {

    private static $languages_json_path = GLPI_ROOT . "/ng/languages/languages.json";



    /**
    * Get all available languages
    *
    * @since ITSM 2.0.0
    *
    * @return array
    */
    public static function getLanguages(): array{

    $languages = [];
    foreach (json_decode(file_get_contents(self::$languages_json_path), true) as $code => $language) {
        if (isset($language['file']) && is_file(GLPI_ROOT . "/locales/" . $language['file'])) {
            $languages[$code] = $language;
        }
    }
    return $languages;
    }

    /**
    * Get available languages by regions, currently only Europe and Others
    * 
    * @since ITSM 2.0.0
    *
    * @return array
    */
    public static function getLanguagesByRegion(){

        $regions = [
            'europe' => [],
            'others' => [] 
        ];

        $european_languages_code = [                                        
            'bd_BG','ca_ES','cz_CZ','de_DE','da_DK','et_EE','en_GB','eu_ES','fr_FR','fr_BE','es_ES',
            'gl_ES','el_GR','hr_HR','hu_HU','it_IT','lv_LV','lt_LT','nl_NL','nl_BE',
            'nb_NO','nn_NO','pt_PT','ro_RO','ru_RU','sk_SK','sl_SI','sr_RS','fi_FI',
            'sv_SE','tr_TR','uk_UA','be_BY','is_IS'];

        $languages = self::getLanguages();

        foreach($languages as $code => $language) {
            if (in_array($code, $european_languages_code)){
                $regions['europe'][] = [$code => $language];
            } else {
                $regions['others'][] = [$code => $language];
            }
        }
        return $regions;
        }
}