<?php

/**
 * 
 * ITSM-NG
 *  
 */

class Language {

    const EMPTY_VALUE = '-----';

    /**
    * Get all available languages
    *
    * @since ITSM 2.0.0
    *
    * @return array
    */
    public static function getLanguages(): array{
        global $CFG_GLPI;

        foreach ($CFG_GLPI["languages"] as $key => $val) {
            if (isset($val[1]) && is_file(GLPI_ROOT ."/locales/".$val[1])) {
            $languages[$key] = $val[0];
            }
        }
        return $languages;
    }

    /**
    * Dropdown available languages
    *
    * @param array  $options  array of additionnal options:
    *    - display_emptychoice : allow selection of no language
    *    - emptylabel          : specific string to empty label if display_emptychoice is true
    **/
   static function showLanguages($options = []) : Array {
    $values = [];
    if (isset($options['display_emptychoice']) && ($options['display_emptychoice'])) {
       if (isset($options['emptylabel'])) {
          $values[''] = $options['emptylabel'];
       } else {
          $values[''] = self::EMPTY_VALUE;
       }
       unset($options['display_emptychoice']);
    }

    $values = array_merge($values, self::getLanguages());
    return $values;
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