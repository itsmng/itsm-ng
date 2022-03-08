<?php

/**
 * Update ITSM-NG from 1.0.0 to 1.0.1
 *
 * @return bool for success (will die for most error)
 **/
function update100to101() {
   /** @global Migration $migration */
   global $DB, $migration, $CFG_GLPI;

   $current_config   = Config::getConfigurationValues('core');
   $updateresult     = true;
   $ADDTODISPLAYPREF = [];

   //TRANS: %s is the number of new version
   $migration->displayTitle(sprintf(__('Update to %s'), '1.0.1'));
   $migration->setVersion('9.5.7');

   /** Replace auror values where glpi_configs.name field = palette */
   $migration->addPostQuery(
       $DB->buildUpdate(
           'glpi_configs',
           ['value' => 'itsmng'],
           ['name' => 'palette']
       )
   );
   /** /Replace auror values where glpi_configs.name field = palette */

   // ************ Keep it at the end **************
   $migration->executeMigration();

   return $updateresult;
}
