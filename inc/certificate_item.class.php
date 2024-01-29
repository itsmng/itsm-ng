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
 * Class to link a certificate to an item
 */
class Certificate_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1    = "Certificate";
   static public $items_id_1    = 'certificates_id';
   static public $take_entity_1 = false;

   static public $itemtype_2    = 'itemtype';
   static public $items_id_2    = 'items_id';
   static public $take_entity_2 = true;

   /**
    * @since 9.2
    *
   **/
   function getForbiddenStandardMassiveAction() {
      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   /**
    * @param CommonDBTM $item
    */
   static function cleanForItem(CommonDBTM $item) {
      $temp = new self();
      $temp->deleteByCriteria(['itemtype' => $item->getType(),
                               'items_id' => $item->getField('id')]);
   }

   /**
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'Certificate'
            && count(Certificate::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item', 'Associated items', Session::getPluralNumber()),
                                           self::countForMainItem($item));
            }
            return _n('Associated item', 'Associated items', Session::getPluralNumber());

         } else if (in_array($item->getType(), Certificate::getTypes(true))
            && Certificate::canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(Certificate::getTypeName(2),
                                           self::countForItem($item));
            }
            return Certificate::getTypeName(2);
         }
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1,
                                            $withtemplate = 0) {

      if ($item->getType() == 'Certificate') {
         self::showForCertificate($item);
      } else if (in_array($item->getType(), Certificate::getTypes(true))) {
         self::showForItem($item);
      }
      return true;
   }


   /**
    * @param $certificates_id
    * @param $items_id
    * @param $itemtype
    * @return bool
    */
   function getFromDBbyCertificatesAndItem($certificates_id, $items_id, $itemtype) {

      $certificate  = new self();
      $certificates = $certificate->find([
         'certificates_id' => $certificates_id,
         'itemtype'        => $itemtype,
         'items_id'        => $items_id
      ]);
      if (count($certificates) != 1) {
         return false;
      }

      $cert         = current($certificates);
      $this->fields = $cert;

      return true;
   }

   /**
   * Link a certificate to an item
   *
   * @since 9.2
   * @param $values
   */
   function addItem($values) {

      $this->add(['certificates_id' => $values["certificates_id"],
                  'items_id'        => $values["items_id"],
                  'itemtype'        => $values["itemtype"]]);
   }

   /**
   * Delete a certificate link to an item
   *
   * @since 9.2
   *
   * @param integer $certificates_id the certificate ID
   * @param integer $items_id the item's id
   * @param string $itemtype the itemtype
   */
   function deleteItemByCertificatesAndItem($certificates_id, $items_id, $itemtype) {

      if ($this->getFromDBbyCertificatesAndItem($certificates_id, $items_id,
                                                $itemtype)) {
         $this->delete(['id' => $this->fields["id"]]);
      }
   }

   /**
    * Show items linked to a certificate
    *
    * @since 9.2
    *
    * @param Certificate $certificate Certificate object
    *
    * @return void|boolean (display) Returns false if there is a rights error.
    **/
   public static function showForCertificate(Certificate $certificate) {
      global $CFG_GLPI;

      $instID = $certificate->fields['id'];
      if (!$certificate->can($instID, READ)) {
         return false;
      }
      $canedit = $certificate->can($instID, UPDATE);

      $types_iterator = self::getDistinctTypes($instID, ['itemtype' => Certificate::getTypes(true)]);
      $number = count($types_iterator);

      if ($canedit) {
         $itemtypes = Document::getItemtypesThatCanHave();
         $options = [];
         foreach ($itemtypes as $itemtype) {
            $options[$itemtype] = $itemtype::getTypeName(1);
         };
         $form = [
            'action' => Toolbox::getItemTypeFormURL(__CLASS__),
            'buttons' => [
               [
                  'type' => 'submit',
                  'name' => 'add',
                  'value' => _sx('button', 'Add an item'),
                  'class' => 'btn btn-secondary'
               ]
            ],
            'content' => [
               __('Add an item') => [
                  'visible' => true,
                  'inputs' => [
                     [
                        'type' => 'hidden',
                        'name' => 'certificates_id',
                        'value' => $instID
                     ],
                     __('Type') => [
                        'type' => 'select',
                        'id' => 'dropdown_itemtype',
                        'name' => 'itemtype',
                        'values' => [Dropdown::EMPTY_VALUE] + array_unique($options),
                        'col_lg' => 6,
                        'hooks' => [
                           'change' => <<<JS
                              $.ajax({
                                    method: "POST",
                                    url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                    data: {
                                       itemtype: this.value,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       $('#dropdown_items_id').empty();
                                       $('#dropdown_items_id').append("<option value='" + data[0].id + "'>" + data[0].text + "</option>");
                                       delete data[0];
                                       for (let i = 1; i < data.length; i++) {
                                          const group = $('#dropdown_items_id')
                                             .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                          for (let j = 0; j < data[i].children.length; j++) {
                                             group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                          }
                                       }
                                    }
                                 });
                           JS,
                        ]
                     ],
                     __('Item') => [
                        'type' => 'select',
                        'id' => 'dropdown_items_id',
                        'name' => 'items_id',
                        'values' => [],
                        'col_lg' => 6,
                     ],
                  ]
               ]
            ]
         ];
         renderTwigForm($form);
      }

      if ($canedit && $number) {
         $massiveactionparams = [
            'container' => 'tableForDocumentItem',
            'display_arrow' => false,
         ];
         Html::showMassiveActions($massiveactionparams);
      }
      $fields = [
         _n('Type', 'Types', 1),
         __('Name'),
         Entity::getTypeName(1),
         __('Serial number'),
         __('Inventory number')
      ];
      $values = [];
      while ($type_row = $types_iterator->next()) {
         $itemtype = $type_row['itemtype'];
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }
         if ($item->canView()) {
            $iterator = self::getTypeItems($instID, $itemtype);

            if ($itemtype == 'SoftwareLicense') {
               $soft = new Software();
            }

            while ($data = $iterator->next()) {
               $linkname_extra = "";
               if ($item instanceof ITILFollowup || $item instanceof ITILSolution) {
                  $linkname_extra = "(" . $item::getTypeName(1) . ")";
                  $itemtype = $data['itemtype'];
                  $item = new $itemtype();
                  $item->getFromDB($data['items_id']);
                  $data['id'] = $item->fields['id'];
                  $data['entity'] = $item->fields['entities_id'];
               } else if ($item instanceof CommonITILTask) {
                  $linkname_extra = "(" . CommonITILTask::getTypeName(1) . ")";
                  $itemtype = $item->getItilObjectItemType();
                  $item = new $itemtype();
                  $item->getFromDB($data[$item->getForeignKeyField()]);
                  $data['id'] = $item->fields['id'];
                  $data['entity'] = $item->fields['entities_id'];
               }

               if ($item instanceof CommonITILObject) {
                  $data["name"] = sprintf(__('%1$s: %2$s'), $item->getTypeName(1), $data["id"]);
               }

               if ($itemtype == 'SoftwareLicense') {
                  $soft->getFromDB($data['softwares_id']);
                  $data["name"] = sprintf(__('%1$s - %2$s'), $data["name"],
                                          $soft->fields['name']);
               }
               if ($item instanceof CommonDevice) {
                  $linkname = $data["designation"];
               } else if ($item instanceof Item_Devices) {
                  $linkname = $data["itemtype"];
               } else {
                  $linkname = $data["name"];
               }
               if ($_SESSION["glpiis_ids_visible"]
                     || empty($data["name"])) {
                  $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
               }
               if ($item instanceof Item_Devices) {
                  $tmpitem = new $item::$itemtype_2();
                  if ($tmpitem->getFromDB($data[$item::$items_id_2])) {
                     $linkname = $tmpitem->getLink();
                  }
               }

               $link     = $itemtype::getFormURLWithID($data['id']);
               $name = "<a href='$link'>$linkname $linkname_extra</a>";

               $newData = [
                  $item->getTypeName(1),
                  $name,
                  isset($data['entity']) ? Dropdown::getDropdownName("glpi_entities",
                     $data['entity']) : "-",
                  isset($data["serial"])? "".$data["serial"]."" :"-",
                  isset($data["otherserial"])? "".$data["otherserial"]."" :"-",
               ];
            }
            $values[] = $newData;
         }
      }

      renderTwigTemplate('table.twig', [
         'fields' => $fields,
         'values' => $values,
      ]);
   }

   /**
    * Show certificates associated to an item
    *
    * @since 9.2
    *
    * @param $item  CommonDBTM object for which associated certificates must be displayed
    * @param $withtemplate (default 0)
    *
    * @return bool
    */
   static function showForItem(CommonDBTM $item, $withtemplate = 0) {

      $ID = $item->getField('id');

      if ($item->isNewID($ID)
         || !Certificate::canView() ||
            !$item->can($item->fields['id'], READ)) {
         return false;
      }

      $certificate  = new Certificate();

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit      = $item->canAddItem('Certificate');
      $rand         = mt_rand();
      $is_recursive = $item->isRecursive();

      $iterator = self::getListForItem($item);
      $number   = $iterator->numrows();
      $i        = 0;

      $certificates = [];
      $used         = [];

      foreach ($iterator as $data) {
         $certificates[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

      if ($canedit && $withtemplate < 2) {

         if ($item->maybeRecursive()) {
            $is_recursive = $item->fields['is_recursive'];
         } else {
            $is_recursive = false;
         }
         $entity_restrict = getEntitiesRestrictCriteria("glpi_certificates",
                                                        'entities_id',
                                                        $item->fields['entities_id'],
                                                        $is_recursive);

         $nb = countElementsInTable('glpi_certificates',
                                    [
                                     'is_deleted'  => 0
                                    ] + $entity_restrict);

         echo "<div class='firstbloc'>";

         if (Certificate::canView() && (!$nb || ($nb > count($used)))) {
            echo "<form name='certificate_form$rand'
                        id='certificate_form$rand'
                        method='post'
                        action='" . Toolbox::getItemTypeFormURL('Certificate_Item')
              . "'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo Html::hidden('entities_id',
                              ['value' => $item->fields['entities_id']]);
            echo Html::hidden('is_recursive',
                              ['value' => $is_recursive]);
            echo Html::hidden('itemtype',
                              ['value' => $item->getType()]);
            echo Html::hidden('items_id',
                              ['value' => $ID]);
            if ($item->getType() == 'Ticket') {
               echo Html::hidden('tickets_id', ['value' => $ID]);
            }
            Dropdown::show('Certificate', ['entity' => $item->fields['entities_id'],
                               'is_recursive'       => $is_recursive,
                               'used'               => $used
                            ]);

            echo "</td><td class='center' width='20%'>";
            echo Html::submit(_sx('button', 'Associate'), ['name' => 'add']);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams = ['num_displayed' => $number];
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>";
         echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         echo "</th>";
      }
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . Entity::getTypeName(1) . "</th>";
      }
      echo "<th>" . _n('Type', 'Types', 1) . "</th>";
      echo "<th>" . __('DNS name') . "</th>";
      echo "<th>" . __('DNS suffix') . "</th>";
      echo "<th>" . __('Creation date') . "</th>";
      echo "<th>" . __('Expiration date') . "</th>";
      echo "<th>" . __('Status') . "</th>";
      echo "</tr>";

      $used = [];

      if ($number) {
         Session::initNavigateListItems('Certificate',
                                        sprintf(__('%1$s = %2$s'),
                                        $item->getTypeName(1), $item->getName()));

         foreach ($certificates as $data) {
            $certificateID = $data["id"];
            $link = NOT_AVAILABLE;

            if ($certificate->getFromDB($certificateID)) {
               $link = $certificate->getLink();
            }

            Session::addToNavigateListItems('Certificate', $certificateID);

            $used[$certificateID] = $certificateID;

            echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                  "</td>";
            }
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_certificatetypes",
               $data["certificatetypes_id"]);
            echo "</td>";
            echo "<td class='center'>" . $data["dns_name"] . "</td>";
            echo "<td class='center'>" . $data["dns_suffix"] . "</td>";
            echo "<td class='center'>" . Html::convDate($data["date_creation"]) . "</td>";
            if ($data["date_expiration"] <= date('Y-m-d')
               && !empty($data["date_expiration"])
            ) {
               echo "<td class='center'>";
               echo "<div class='deleted'>" . Html::convDate($data["date_expiration"]) . "</div>";
               echo "</td>";
            } else if (empty($data["date_expiration"])) {
               echo "<td class='center'>" . __('Does not expire') . "</td>";
            } else {
               echo "<td class='center'>" . Html::convDate($data["date_expiration"]) . "</td>";
            }
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_states", $data["states_id"]);
            echo "</td>";
            echo "</tr>";
            $i++;
         }
      }

      echo "</table>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }
}
