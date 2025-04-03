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
class Certificate_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1    = "Certificate";
    public static $items_id_1    = 'certificates_id';
    public static $take_entity_1 = false;

    public static $itemtype_2    = 'itemtype';
    public static $items_id_2    = 'items_id';
    public static $take_entity_2 = true;

    /**
     * @since 9.2
     *
    **/
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param CommonDBTM $item
     */
    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['itemtype' => $item->getType(),
                                 'items_id' => $item->getField('id')]);
    }

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            if (
                $item->getType() == 'Certificate'
                && count(Certificate::getTypes(false))
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        _n('Associated item', 'Associated items', Session::getPluralNumber()),
                        self::countForMainItem($item)
                    );
                }
                return _n('Associated item', 'Associated items', Session::getPluralNumber());
            } elseif (
                in_array($item->getType(), Certificate::getTypes(true))
                && Certificate::canView()
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        Certificate::getTypeName(2),
                        self::countForItem($item)
                    );
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
    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {

        if ($item->getType() == 'Certificate') {
            self::showForCertificate($item);
        } elseif (in_array($item->getType(), Certificate::getTypes(true))) {
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
    public function getFromDBbyCertificatesAndItem($certificates_id, $items_id, $itemtype)
    {

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
    public function addItem($values)
    {

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
    public function deleteItemByCertificatesAndItem($certificates_id, $items_id, $itemtype)
    {

        if (
            $this->getFromDBbyCertificatesAndItem(
                $certificates_id,
                $items_id,
                $itemtype
            )
        ) {
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
    public static function showForCertificate(Certificate $certificate)
    {
        global $CFG_GLPI;

        $instID = $certificate->fields['id'];
        if (!$certificate->can($instID, READ)) {
            return false;
        }
        $canedit = $certificate->can($instID, UPDATE);

        $types_iterator = self::getDistinctTypes($instID, ['itemtype' => Certificate::getTypes()]);
        $number = count($types_iterator);

        if ($canedit) {
            $itemtypes = Certificate::getTypes(true);
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
                                       display_emptychoice: 1,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       $('#dropdown_items_id').empty();
                                       for (let i = 0; i < data.length; i++) {
                                          if (data[i].children) {
                                             const group = $('#dropdown_items_id')
                                                .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                             for (let j = 0; j < data[i].children.length; j++) {
                                                group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                             }
                                          } else {
                                             $('#dropdown_items_id').append("<option value='" + data[i].id + "'>" + data[i].text + "</option>");
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
               'container' => 'tableForCertificateItem',
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
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
        $massiveactionValues = [];
        foreach ($types_iterator as $type_row) {
            $itemtype = $type_row['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView()) {
                $iterator = self::getTypeItems($instID, $itemtype);

                if ($itemtype == 'SoftwareLicense') {
                    $soft = new Software();
                }

                foreach ($iterator as $data) {
                    $linkname_extra = "";
                    if ($item instanceof ITILFollowup || $item instanceof ITILSolution) {
                        $linkname_extra = "(" . $item::getTypeName(1) . ")";
                        $itemtype = $data['itemtype'];
                        $item = new $itemtype();
                        $item->getFromDB($data['items_id']);
                        $data['id'] = $item->fields['id'];
                        $data['entity'] = $item->fields['entities_id'];
                    } elseif ($item instanceof CommonITILTask) {
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
                        $data["name"] = sprintf(
                            __('%1$s - %2$s'),
                            $data["name"],
                            $soft->fields['name']
                        );
                    }
                    if ($item instanceof CommonDevice) {
                        $linkname = $data["designation"];
                    } elseif ($item instanceof Item_Devices) {
                        $linkname = $data["itemtype"];
                    } else {
                        $linkname = $data["name"];
                    }
                    if (
                        $_SESSION["glpiis_ids_visible"]
                          || empty($data["name"])
                    ) {
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
                       isset($data['entity']) ? Dropdown::getDropdownName(
                           "glpi_entities",
                           $data['entity']
                       ) : "-",
                       isset($data["serial"]) ? "" . $data["serial"] . "" : "-",
                       isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-",
                    ];
                    $values[] = $newData;
                    $massiveactionValues[] = sprintf('item[%s][%s]', $itemtype, $data['id']);
                }
            }
        }

        renderTwigTemplate('table.twig', [
           'id' => 'tableForCertificateItem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
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
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {

        $ID = $item->getField('id');

        if (
            $item->isNewID($ID)
            || !Certificate::canView()
              || !$item->can($item->fields['id'], READ)
        ) {
            return false;
        }

        $certificate  = new Certificate();

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        $canedit      = $item->canAddItem('Certificate');
        $is_recursive = $item->isRecursive();

        $iterator = self::getListForItem($item);
        $number   = count($iterator);
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
            $entity_restrict = getEntitiesRestrictCriteria(
                "glpi_certificates",
                'entities_id',
                $item->fields['entities_id'],
                $is_recursive
            );

            $nb = countElementsInTable(
                'glpi_certificates',
                [
                                        'is_deleted'  => 0
                                       ] + $entity_restrict
            );

            if (Certificate::canView() && (!$nb || ($nb > count($used)))) {
                $form = [
                   'action' => Toolbox::getItemTypeFormURL('Certificate_Item'),
                   'buttons' => [
                      [
                         'name' => 'add',
                         'value' => _x('button', 'Associate'),
                         'class' => 'btn btn-secondary',
                      ]
                   ],
                   'content' => [
                      '' => [
                         'visible' => true,
                         'inputs' => [
                            [
                               'type' => 'hidden',
                               'name' => 'entities_id',
                               'value' => $item->fields['entities_id']
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'is_recursive',
                               'value' => $item->fields['is_recursive']
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'itemtype',
                               'value' => $item->getType(),
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'items_id',
                               'value' => $item->fields['id']
                            ],
                            ($item->getType() == 'Ticket') ? [
                               'type' => 'hidden',
                               'name' => 'tickets_id',
                               'value' => $ID
                            ] : [],
                            '' => [
                               'type' => 'select',
                               'name' => 'certificates_id',
                               'itemtype' => Certificate::class,
                               'condition' => ['is_deleted' => 0, 'entities_id' => $item->fields['entities_id']],
                               'actions' => getItemActionButtons(['info'], Certificate::class),
                               'col_lg' => 12,
                               'col_md' => 12,
                            ]
                         ]
                      ]
                   ]
                ];
                renderTwigForm($form);
            }
        }

        if ($canedit && $number && ($withtemplate < 2)) {
            $massformContainerId = 'tableForCertificateItem';
            $massiveactionparams = [
               'container' => $massformContainerId,
               'display_arrow' => false,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           _n('Type', 'Types', 1),
           __('DNS name'),
           __('DNS suffix'),
           __('Creation date'),
           __('Expiration date'),
           __('Status')
        ];
        if (Session::isMultiEntitiesMode()) {
            $fields[] = Entity::getTypeName(1);
        }
        $values = [];
        $massive_action = [];
        foreach ($certificates as $data) {
            $newValue = [];
            $certificateID = $data["id"];
            $link = NOT_AVAILABLE;

            if ($certificate->getFromDB($certificateID)) {
                $link = $certificate->getLink();
            }
            $used[$certificateID] = $certificateID;
            $newValue = [
               $link,
               Dropdown::getDropdownName("glpi_certificatetypes", $data["certificatetypes_id"]),
               $data["dns_name"],
               $data["dns_suffix"],
               Html::convDate($data["date_creation"]),

            ];

            if (
                $data["date_expiration"] <= date('Y-m-d')
                && !empty($data["date_expiration"])
            ) {
                $newValue[] = Html::convDate($data["date_expiration"]);
            } elseif (empty($data["date_expiration"])) {
                $newValue[] = __('Does not expire');
            } else {
                $newValue[] = Html::convDate($data["date_expiration"]);
            }
            $newValue[] = Dropdown::getDropdownName("glpi_states", $data["states_id"]);
            if (Session::isMultiEntitiesMode()) {
                $newValue[] = Dropdown::getDropdownName("glpi_entities", $data['entities_id']);
            }

            $i++;

            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => $massformContainerId ?? '',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }
}
