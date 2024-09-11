<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Common DataBase visibility for items
 */
abstract class CommonDBVisible extends CommonDBTM {

   /**
    * Is the login user have access to item based on visibility configuration
    *
    * @since 0.83
    * @since 9.2 moved from each class to parent class
    *
    * @return boolean
   **/
   public function haveVisibilityAccess() {
      // Author
      if ($this->fields['users_id'] == Session::getLoginUserID()) {
         return true;
      }
      // Users
      if (isset($this->users[Session::getLoginUserID()])) {
         return true;
      }

      // Groups
      if (count($this->groups)
          && isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"])) {
         foreach ($this->groups as $data) {
            foreach ($data as $group) {
               if (in_array($group['groups_id'], $_SESSION["glpigroups"])) {
                  // All the group
                  if ($group['entities_id'] < 0) {
                     return true;
                  }
                  // Restrict to entities
                  if (Session::haveAccessToEntity($group['entities_id'], $group['is_recursive'])) {
                     return true;
                  }
               }
            }
         }
      }

      // Entities
      if (count($this->entities)
          && isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {
         foreach ($this->entities as $data) {
            foreach ($data as $entity) {
               if (Session::haveAccessToEntity($entity['entities_id'], $entity['is_recursive'])) {
                  return true;
               }
            }
         }
      }

      // Profiles
      if (count($this->profiles)
          && isset($_SESSION["glpiactiveprofile"])
          && isset($_SESSION["glpiactiveprofile"]['id'])) {
         if (isset($this->profiles[$_SESSION["glpiactiveprofile"]['id']])) {
            foreach ($this->profiles[$_SESSION["glpiactiveprofile"]['id']] as $profile) {
               // All the profile
               if ($profile['entities_id'] < 0) {
                  return true;
               }
               // Restrict to entities
               if (Session::haveAccessToEntity($profile['entities_id'], $profile['is_recursive'])) {
                  return true;
               }
            }
         }
      }

      return false;
   }

   /**
    * Count visibilities
    *
    * @since 0.83
    * @since 9.2 moved from each class to parent class
    *
    * @return integer
    */
   public function countVisibilities() {

      return (count($this->entities)
              + count($this->users)
              + count($this->groups)
              + count($this->profiles));
   }

   /**
    * Show visibility configuration
    *
    * @since 9.2 moved from each class to parent class
    *
    * @return void
   **/
   public function showVisibility() {
      global $CFG_GLPI;

      $ID      = $this->fields['id'];
      $canedit = $this->canEdit($ID);
      $rand    = mt_rand();
      $nb      = $this->countVisibilities();
      $str_type = strtolower($this::getType());
      $fk = static::getForeignKeyField();

      if ($canedit) {
         $form = [
            'action' => static::getFormURL(),
            'buttons' => [[
               'type' => 'submit',
               'name' => 'addvisibility',
               'value' => __('Add'),
               'class' => 'btn btn-secondary'
            ]],
            'content' => [
               __('Add a target') => [
                  'visible' => true,
                  'inputs' => [
                     [
                        'type' => 'hidden',
                        'name' => $fk,
                        'value' => $ID
                     ],
                     __('Type') => [
                        'type' => 'select',
                        'name' => '_type',
                        'id' => 'selectForType'.$rand,
                        'values' => [ Dropdown::EMPTY_VALUE,
                           'Entity' => 'Entity',
                           'Group' => 'Group',
                           'Profile' => 'Profile',
                           'User' => 'User'
                        ],
                        'col_lg' => 6,
                        'hooks' => [
                           'change' => <<<JS
                           var type = jQuery(this).val();
                           // empty value -> disable all
                           // entity -> enable entity and checkbox, disable others
                           // * -> enable all
                           $("#selectForEntity$rand").prop("disabled", type == 0);
                           $("#checkboxForChildEntities$rand").prop("disabled", type == 0);
                           $("#selectForTarget$rand").prop("disabled", type == 0 || type == "Entity");
                           if (type == 0 || type == "Entity") {
                              return;
                           }
                           $.ajax({
                              url: "{$CFG_GLPI['root_doc']}/ajax/visibility.php",
                              method: "POST",
                              data: {
                                 type: type,
                                 right: "{$str_type}_public"
                              },
                              success: function(data) {
                                 const jsonData = JSON.parse(data);
                                 $("#selectForTarget$rand").empty();
                                 $("#selectForTarget$rand").attr("name", type.toLowerCase() + "s_id");
                                 for (const [key, value] of Object.entries(jsonData)) {
                                    if (typeof(value) === "object") {
                                        const optgroup = $("<optgroup></optgroup>").attr("label", key);
                                        for (const [k, v] of Object.entries(value)) {
                                           optgroup.append(
                                              $("<option></option>")
                                                 .attr("value", k)
                                                 .text(v)
                                           );
                                        }
                                        $("#selectForTarget$rand").append(optgroup);
                                    } else {
                                        $("#selectForTarget$rand").append(
                                           $("<option></option>")
                                              .attr("value", key)
                                              .text(value)
                                        );
                                    }
                                 }
                              }
                           });
                           JS,
                        ]
                     ],
                     __('Target') => [
                        'type' => 'select',
                        'id' => "selectForTarget$rand",
                        'col_lg' => 6,
                        'disabled' => '',
                     ],
                     __('Entity') => [
                        'type' => 'select',
                        'id' => "selectForEntity$rand",
                        'name' => 'entities_id',
                        'values' => getOptionForItems(Entity::class),
                        'value' => Session::getActiveEntity(),
                        'disabled' => '',
                        'col_lg' => 6,
                     ],
                     __('Child entities') => [
                        'type' => 'checkbox',
                        'name' => 'is_recursive',
                        'id' => "checkboxForChildEntities$rand",
                        'disabled' => '',
                        'col_lg' => 6,
                     ],
                  ]
               ]
            ]
         ];
         renderTwigForm($form);
      }
      $massContainerId = 'mass'.__CLASS__.$rand;
      if ($canedit && $nb) {
         $massiveactionparams = [
            'num_displayed' => min($_SESSION['glpilist_limit'], $nb),
            'container' => $massContainerId,
            'specific_actions' => ['delete' => _x('button', 'Delete permanently')],
            'display_arrow' => false,
         ];
         
         if ($this->fields['users_id'] != Session::getLoginUserID()) {
            $massiveactionparams['confirm']
            = __('Caution! You are not the author of this element. Delete targets can result in loss of access to that element.');
         }
         Html::showMassiveActions($massiveactionparams);
      }
      $fields = [
         _n('Type', 'Types', 1),
         _n('Recipient', 'Recipients', Session::getPluralNumber()),
      ];
      $values = [];
      $massive_action = [];
      // Users
      if (count($this->users)) {
         foreach ($this->users as $val) {
            foreach ($val as $data) {
               $values[] = [
                  User::getTypeName(1),
                  getUserName($data['users_id']),
               ];
               $massive_action[] = sprintf('item[%s][%s]', Reminder_User::class, $data['id']);
            }
         }
      }
      // Groups
      if (count($this->groups)) {
         foreach ($this->groups as $val) {
            foreach ($val as $data) {
               $names   = Dropdown::getDropdownName('glpi_groups', $data['groups_id'], 1);
               $entname = sprintf(__('%1$s %2$s'), $names["name"],
                                    Html::showToolTip($names["comment"], ['display' => false]));
               if ($data['entities_id'] >= 0) {
                  $entname = sprintf(__('%1$s / %2$s'), $entname,
                                       Dropdown::getDropdownName('glpi_entities',
                                                               $data['entities_id']));
                  if ($data['is_recursive']) {
                     //TRANS: R for Recursive
                     $entname = sprintf(__('%1$s %2$s'),
                                          $entname, "<span class='b'>(".__('R').")</span>");
                  }
               }
               $values[] = [
                  Group::getTypeName(1),
                  $entname,
               ];
               $massive_action[] = sprintf('item[%s][%s]', 'Group_' . $this->getType(), $data['id']);
            }
         }
      }
      // Entity
      if (count($this->entities)) {
         foreach ($this->entities as $val) {
            foreach ($val as $data) {
               $names   = Dropdown::getDropdownName('glpi_entities', $data['entities_id'], 1);
               $tooltip = Html::showToolTip($names["comment"], ['display' => false]);
               $entname = sprintf(__('%1$s %2$s'), $names["name"], $tooltip);
               if ($data['is_recursive']) {
                  $entname = sprintf(__('%1$s %2$s'), $entname,
                                       "<span class='b'>(".__('R').")</span>");
               }
               $values[] = [
                  Entity::getTypeName(1),
                  $entname,
               ];
               $massive_action[] = sprintf('item[%s][%s]', 'Entity_' . $this->getType(), $data['id']);
            }
         }
      }
      // Profiles
      if (count($this->profiles)) {
         foreach ($this->profiles as $val) {
            foreach ($val as $data) {
               $names   = Dropdown::getDropdownName('glpi_profiles', $data['profiles_id'], 1);
               $tooltip = Html::showToolTip($names["comment"], ['display' => false]);
               $entname = sprintf(__('%1$s %2$s'), $names["name"], $tooltip);
               if ($data['entities_id'] >= 0) {
                  $entname = sprintf(__('%1$s / %2$s'), $entname,
                                       Dropdown::getDropdownName('glpi_entities',
                                                                  $data['entities_id']));
                  if ($data['is_recursive']) {
                     $entname = sprintf(__('%1$s %2$s'), $entname,
                                          "<span class='b'>(".__('R').")</span>");
                  }
               }
               $values[] = [
                  Profile::getTypeName(1),
                  $entname,
               ];
               $massive_action[] = sprintf('item[%s][%s]', 'Profile_'.$this->getType(), $data['id']);
            }
         }
      }
      renderTwigTemplate('table.twig', [
         'id' => $massContainerId,
         'fields' => $fields,
         'values' => $values,
         'massive_action' => $massive_action,
      ]);
      return true;
   }

   /**
    * Get dropdown parameters from showVisibility method
    *
    * @return array
    */
   protected function getShowVisibilityDropdownParams() {
      return [
         'type'  => '__VALUE__',
         'right' => strtolower($this::getType()) . '_public'
      ];
   }
}
