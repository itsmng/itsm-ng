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
 * Document_Item Class
 *
 *  Relation between Documents and Items
**/
class Document_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1    = 'Document';
    public static $items_id_1    = 'documents_id';
    public static $take_entity_1 = true;

    public static $itemtype_2    = 'itemtype';
    public static $items_id_2    = 'items_id';
    public static $take_entity_2 = false;


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function canCreateItem()
    {

        if ($this->fields['itemtype'] == 'Ticket') {
            $ticket = new Ticket();
            // Not item linked for closed tickets
            if (
                $ticket->getFromDB($this->fields['items_id'])
                && in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
            ) {
                return false;
            }
        }

        return parent::canCreateItem();
    }


    public function prepareInputForAdd($input)
    {

        if (empty($input['itemtype'])) {
            Toolbox::logError('Item type is mandatory');
            return false;
        }

        if (!class_exists($input['itemtype'])) {
            Toolbox::logError(sprintf('No class found for type %s', $input['itemtype']));
            return false;
        }

        if (
            (empty($input['items_id']))
            && ($input['itemtype'] != 'Entity')
        ) {
            Toolbox::logError('Item ID is mandatory');
            return false;
        }

        if (empty($input['documents_id'])) {
            Toolbox::logError('Document ID is mandatory');
            return false;
        }

        // Do not insert circular link for document
        if (
            ($input['itemtype'] == 'Document')
            && ($input['items_id'] == $input['documents_id'])
        ) {
            Toolbox::logError('Cannot link document to itself');
            return false;
        }

        // #1476 - Inject ID of the actual user to known who attach an already existing document
        // to another item
        if (!isset($input['users_id'])) {
            $input['users_id'] = Session::getLoginUserID();
        }

        /** FIXME: should not this be handled on CommonITILObject side? */
        if (is_subclass_of($input['itemtype'], 'CommonITILObject') && !isset($input['timeline_position'])) {
            $input['timeline_position'] = CommonITILObject::TIMELINE_LEFT;
            if (isset($input["users_id"])) {
                $input['timeline_position'] = $input['itemtype']::getTimelinePosition($input['items_id'], $this->getType(), $input["users_id"]);
            }
        }

        // Avoid duplicate entry
        if ($this->alreadyExists($input)) {
            Toolbox::logError('Duplicated document item relation');
            return false;
        }

        return parent::prepareInputForAdd($input);
    }

    /**
     * Check if relation already exists.
     *
     * @param array $input
     *
     * @return boolean
     *
     * @since 9.5.0
     */
    public function alreadyExists(array $input): bool
    {
        $criteria = [
           'documents_id'      => $input['documents_id'],
           'itemtype'          => $input['itemtype'],
           'items_id'          => $input['items_id'],
           'timeline_position' => $input['timeline_position'] ?? null
        ];
        if (array_key_exists('timeline_position', $input) && !empty($input['timeline_position'])) {
            $criteria['timeline_position'] = $input['timeline_position'];
        }
        return countElementsInTable($this->getTable(), $criteria) > 0;
    }


    /**
     * @since 0.90.2
     *
     * @see CommonDBTM::pre_deleteItem()
    **/
    public function pre_deleteItem()
    {
        // fordocument mandatory
        if ($this->fields['itemtype'] == 'Ticket') {
            $ticket = new Ticket();
            $ticket->getFromDB($this->fields['items_id']);

            $tt = $ticket->getITILTemplateToUse(
                0,
                $ticket->fields['type'],
                $ticket->fields['itilcategories_id'],
                $ticket->fields['entities_id']
            );

            if (isset($tt->mandatory['_documents_id'])) {
                // refuse delete if only one document
                if (
                    countElementsInTable(
                        $this->getTable(),
                        ['items_id' => $this->fields['items_id'],
                                         'itemtype' => 'Ticket' ]
                    ) == 1
                ) {
                    $message = sprintf(
                        __('Mandatory fields are not filled. Please correct: %s'),
                        Document::getTypeName(Session::getPluralNumber())
                    );
                    Session::addMessageAfterRedirect($message, false, ERROR);
                    return false;
                }
            }
        }
        return true;
    }


    public function post_addItem()
    {

        if ($this->fields['itemtype'] == 'Ticket' && ($this->input['_do_update_ticket'] ?? true)) {
            $ticket = new Ticket();
            $input  = [
               'id'              => $this->fields['items_id'],
               'date_mod'        => $_SESSION["glpi_currenttime"],
               '_donotadddocs'   => true];

            if (!isset($this->input['_do_notif']) || $this->input['_do_notif']) {
                $input['_forcenotif'] = true;
            }
            if (isset($this->input['_disablenotif']) && $this->input['_disablenotif']) {
                $input['_disablenotif'] = true;
            }

            $ticket->update($input);
        }
        parent::post_addItem();
    }


    /**
     * @since 0.83
     *
     * @see CommonDBTM::post_purgeItem()
    **/
    public function post_purgeItem()
    {

        if ($this->fields['itemtype'] == 'Ticket') {
            $ticket = new Ticket();
            $input = [
               'id'              => $this->fields['items_id'],
               'date_mod'        => $_SESSION["glpi_currenttime"],
               '_donotadddocs'   => true];

            if (!isset($this->input['_do_notif']) || $this->input['_do_notif']) {
                $input['_forcenotif'] = true;
            }

            //Clean ticket description if an image is in it
            $doc = new Document();
            $doc->getFromDB($this->fields['documents_id']);
            if (!empty($doc->fields['tag'])) {
                $ticket->getFromDB($this->fields['items_id']);
                $input['content'] = Toolbox::addslashes_deep(
                    Toolbox::cleanTagOrImage(
                        $ticket->fields['content'],
                        [$doc->fields['tag']]
                    )
                );
            }

            $ticket->update($input);
        }
        parent::post_purgeItem();
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $nbdoc = $nbitem = 0;
        switch ($item->getType()) {
            case 'Document':
                $ong = [];
                if ($_SESSION['glpishow_count_on_tabs'] && !$item->isNewItem()) {
                    $nbdoc  = self::countForMainItem($item, ['NOT' => ['itemtype' => 'Document']]);
                    $nbitem = self::countForMainItem($item, ['itemtype' => 'Document']);
                }
                $ong[1] = self::createTabEntry(_n(
                    'Associated item',
                    'Associated items',
                    Session::getPluralNumber()
                ), $nbdoc);
                $ong[2] = self::createTabEntry(
                    Document::getTypeName(Session::getPluralNumber()),
                    $nbitem
                );
                return $ong;

            default:
                // Can exist for template
                if (
                    Document::canView()
                    || ($item->getType() == 'Ticket')
                    || ($item->getType() == 'Reminder')
                    || ($item->getType() == 'KnowbaseItem')
                ) {
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nbitem = self::countForItem($item);
                    }
                    return self::createTabEntry(
                        Document::getTypeName(Session::getPluralNumber()),
                        $nbitem
                    );
                }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Document':
                switch ($tabnum) {
                    case 1:
                        self::showForDocument($item);
                        break;

                    case 2:
                        self::showForItem($item, $withtemplate);
                        break;
                }
                return true;

            default:
                self::showForitem($item, $withtemplate);
        }
    }


    /**
     * Duplicate documents from an item template to its clone
     *
     * @deprecated 9.5
     * @since 0.84
     *
     * @param string  $itemtype     itemtype of the item
     * @param integer $oldid        ID of the item to clone
     * @param integer $newid        ID of the item cloned
     * @param string  $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
    **/
    public static function cloneItem($itemtype, $oldid, $newid, $newitemtype = '')
    {
        Toolbox::deprecated('Use clone');
        if (empty($newitemtype)) {
            $newitemtype = $itemtype;
        }

        $result = self::getAdapter()->request([
           'FIELDS' => ['documents_id'],
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'items_id'  => $oldid,
              'itemtype'  => $itemtype
           ]
        ]);
        while ($data = $result->fetchAssociative()) {
            $docitem = new self();
            $docitem->add(
                [
                'documents_id' => $data["documents_id"],
                'itemtype'     => $newitemtype,
                'items_id'     => $newid]
            );
        }
    }


    /**
     * Show items links to a document
     *
     * @since 0.84
     *
     * @param $doc Document object
     *
     * @return void
    **/
    public static function showForDocument(Document $doc)
    {
        global $CFG_GLPI;

        $instID = $doc->fields['id'];
        if (!$doc->can($instID, READ)) {
            return false;
        }
        $canedit = $doc->can($instID, UPDATE);
        // for a document,
        // don't show here others documents associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = self::getDistinctTypes($instID, ['NOT' => ['itemtype' => 'Document']]);
        $number = count($types_iterator);

        $rand   = mt_rand();
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
                           'name' => 'documents_id',
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
               'container'        => 'tableForDocumentItem',
               'specific_actions' => [
                  'purge' => _x('button', 'Delete permanently')
               ],
               'display_arrow' => false
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

                while ($data = $iterator->next()) {
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
           'id' => 'tableForDocumentItem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }

    /**
     * Show documents associated to an item
     *
     * @since 0.84
     *
     * @param $item            CommonDBTM object for which associated documents must be displayed
     * @param $withtemplate    (default 0)
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        $ID = $item->getField('id');

        if ($item->isNewID($ID)) {
            return false;
        }

        if (
            ($item->getType() != 'Ticket')
            && ($item->getType() != 'KnowbaseItem')
            && ($item->getType() != 'Reminder')
            && !Document::canView()
        ) {
            return false;
        }

        $params         = [];
        $params['rand'] = mt_rand();

        self::showAddFormForItem($item, $withtemplate, $params);
        self::showListForItem($item, $withtemplate, $params);
    }


    /**
     * @since 0.90
     *
     * @param $item
     * @param $withtemplate   (default 0)
     * @param $colspan
    */
    public static function showSimpleAddForItem(CommonDBTM $item, $withtemplate = 0, $colspan = 1)
    {

        $entity = $_SESSION["glpiactive_entity"];
        if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >= 0) {
                $entity = $item->getEntityID();
            }
        }

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Add a document') . "</td>";
        echo "<td colspan='$colspan'>";
        echo "<input type='hidden' name='entities_id' value='$entity'>";
        echo "<input type='hidden' name='is_recursive' value='" . $item->isRecursive() . "'>";
        echo "<input type='hidden' name='itemtype' value='" . $item->getType() . "'>";
        echo "<input type='hidden' name='items_id' value='" . $item->getID() . "'>";
        if ($item->getType() == 'Ticket') {
            echo "<input type='hidden' name='tickets_id' value='" . $item->getID() . "'>";
        }
        Html::file(['multiple' => true]);
        echo "</td><td class='left'>(" . Document::getMaxUploadSize() . ")&nbsp;</td>";
        echo "<td></td></tr>";
    }


    /**
     * @since 0.90
     *
     * @param $item
     * @param $withtemplate    (default 0)
     * @param $options         array
     *
     * @return boolean
    **/
    public static function showAddFormForItem(CommonDBTM $item, $withtemplate = 0, $options = [])
    {
        global $CFG_GLPI;

        //default options
        $params['rand'] = mt_rand();
        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        if (!$item->can($item->fields['id'], READ)) {
            return false;
        }

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        // find documents already associated to the item
        $doc_item   = new self();
        $used_found = $doc_item->find([
           'items_id'  => $item->getID(),
           'itemtype'  => $item->getType()
        ]);
        $used       = array_keys($used_found);
        $used       = array_combine($used, $used);

        if (
            $item->canAddItem('Document')
            && $withtemplate < 2
        ) {
            // Restrict entity for knowbase
            $entities = "";
            $entity   = $_SESSION["glpiactive_entity"];

            if ($item->isEntityAssign()) {
                /// Case of personal items : entity = -1 : create on active entity (Reminder case))
                if ($item->getEntityID() >= 0) {
                    $entity = $item->getEntityID();
                }

                if ($item->isRecursive()) {
                    $entities = getSonsOf('glpi_entities', $entity);
                } else {
                    $entities = $entity;
                }
            }
            $limit = getEntitiesRestrictRequest(" AND ", "glpi_documents", '', $entities, true);

            $count = self::getAdapter()->request([
               'COUNT'     => 'cpt',
               'FROM'      => 'glpi_documents',
               'WHERE'     => [
                  'is_deleted' => 0
               ] + getEntitiesRestrictCriteria('glpi_documents', '', $entities, true)
            ])->fetchAssociative();
            $nb = $count['cpt'];

            if ($item->getType() == 'Document') {
                $used[$item->getID()] = $item->getID();
            }

            $form = [
               'method' => 'post',
               'action' => Toolbox::getItemTypeFormURL('Document'),
               'buttons' => [
                  'submit' => [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add a new file'),
                     'class' => 'btn btn-primary mb-3'
                  ]
               ],
               'content' => [
                  __('Add a document') => [
                     'visible' => true,
                     'inputs' => [
                        __('Heading') => [
                           'type' => 'select',
                           'name' => 'documentcategories_id',
                           'values' => getOptionForItems('DocumentCategory'),
                           'actions' => getItemActionButtons(['info', 'add'], 'DocumentCategory'),
                           'col_lg' => 6,
                        ],
                        __('File') => [
                           'type' => 'file',
                           'name' => 'files',
                           'id' => 'fileSelectorForDocument',
                           'data-max-size' => Document::getMaxUploadSizeInBytes(),
                           'multiple' => 'multiple',
                           'col_lg' => 6,
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'entities_id',
                           'value' => $entity
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'is_recursive',
                           'value' => $item->isRecursive()
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $item->getType()
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'items_id',
                           'value' => $item->getID()
                        ],
                        $item->getType() == 'Ticket' ? [
                           'type' => 'hidden',
                           'name' => 'tickets_id',
                           'value' => $item->getID()
                        ] : [],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);

            if (
                Document::canView()
                && ($nb > count($used))
            ) {
                $values = getItemByEntity(Document::class, $entities);
                $criteria = [
                   'FROM'   => 'glpi_documentcategories',
                   'WHERE'  => [
                      'id' => new QuerySubQuery([
                         'SELECT'          => 'documentcategories_id',
                         'DISTINCT'        => true,
                         'FROM'            => 'glpi_documents',
                      ])
                   ],
                   'ORDER'  => 'name'
                ];
                $result = self::getAdapter()->request($criteria);

                $headings = [];
                while ($data = $result->fetchAssociative()) {
                    $headings[$data['id']] = $data['name'];
                }

                foreach ($used as $id) {
                    unset($values[$id]);
                }
                $form = [
                   'method' => 'post',
                   'action' => Toolbox::getItemTypeFormURL(__CLASS__),
                   'buttons' => [
                      [
                         'type' => 'submit',
                         'name' => 'add',
                         'value' => _sx('button', 'Associate an existing document'),
                         'class' => 'btn btn-secondary'
                      ]
                   ],
                   'content' => [
                      __('Associate an existing document') => [
                         'visible' => true,
                         'inputs' => [
                            __('Heading') => [
                               'type' => 'select',
                               'id' => 'selectForRubDocId',
                               'name' => '_rubdoc',
                               'values' => [Dropdown::EMPTY_VALUE] + $headings,
                               'col_lg' => 6,
                               'hooks' => [
                                  'change' => <<<JS
                              var rubdoc = $('#selectForRubDocId').val();
                              var entity = $entity;
                              $.ajax({
                                 url: "{$CFG_GLPI['root_doc']}/ajax/dropdownRubDocument.php",
                                 method: "POST",
                                 data: {rubdoc: rubdoc, entity: entity},
                                 success: function(data) {
                                    const jsonData = JSON.parse(data);
                                    $('#selectForDocumentId').empty();
                                    for (const i in jsonData) {
                                       $('#selectForDocumentId').append('<option value="' + i + '">' + jsonData[i] + '</option>');
                                    }
                                 }
                              });
                              JS,
                               ]
                            ],
                            Document::getTypeName() => [
                               'type' => 'select',
                               'id' => 'selectForDocumentId',
                               'name' => 'documents_id',
                               'itemtype' => Document::class,
                               'col_lg' => 6,
                               'actions' => getItemActionButtons(['info'], Document::class)
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'itemtype',
                               'value' => $item->getType()
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'items_id',
                               'value' => $item->getID()
                            ],
                            $item->getType() == 'Ticket' ? [
                               'type' => 'hidden',
                               'name' => 'tickets_id',
                               'value' => $item->getID()
                            ] : [],
                            $item->getType() == 'Ticket' ? [
                               'type' => 'hidden',
                               'name' => 'documentcategories_id',
                               'value' => $CFG_GLPI["documentcategories_id_forticket"]
                            ] : [],
                         ]
                      ]
                   ]
                ];
                renderTwigForm($form);
            }
        }
    }


    /**
     * @since 0.90
     *
     * @param $item
     * @param $withtemplate   (default 0)
     * @param $options        array
     */
    public static function showListForItem(CommonDBTM $item, $withtemplate = 0, $options = [])
    {
        global $DB;

        //default options
        $params['rand'] = mt_rand();

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        $canedit = $item->canAddItem('Document') && Document::canView();

        $columns = [
           'name'      => __('Name'),
           'entity'    => Entity::getTypeName(1),
           'filename'  => __('File'),
           'link'      => __('Web link'),
           'headings'  => __('Heading'),
           'mime'      => __('MIME type'),
           'tag'       => __('Tag'),
           'assocdate' => _n('Date', 'Dates', 1)
        ];

        if (isset($_GET["order"]) && ($_GET["order"] == "ASC")) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }

        if (
            (isset($_GET["sort"]) && !empty($_GET["sort"]))
            && isset($columns[$_GET["sort"]])
        ) {
            $sort = $_GET["sort"];
        } else {
            $sort = "assocdate";
        }

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }
        $linkparam = '';

        if (get_class($item) == 'Ticket') {
            $linkparam = "&amp;tickets_id=" . $item->fields['id'];
        }

        $criteria = [
           'SELECT'    => [
              'glpi_documents_items.id AS assocID',
              'glpi_documents_items.date_creation AS assocdate',
              'glpi_entities.id AS entityID',
              'glpi_entities.completename AS entity',
              'glpi_documentcategories.completename AS headings',
              'glpi_documents.*'
           ],
           'FROM'      => 'glpi_documents_items',
           'LEFT JOIN' => [
              'glpi_documents'  => [
                 'ON' => [
                    'glpi_documents_items'  => 'documents_id',
                    'glpi_documents'        => 'id'
                 ]
              ],
              'glpi_entities'   => [
                 'ON' => [
                    'glpi_documents'  => 'entities_id',
                    'glpi_entities'   => 'id'
                 ]
              ],
              'glpi_documentcategories'  => [
                 'ON' => [
                    'glpi_documentcategories'  => 'id',
                    'glpi_documents'           => 'documentcategories_id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_documents_items.items_id'  => $item->getID(),
              'glpi_documents_items.itemtype'  => $item->getType()
           ],
           'ORDERBY'   => [
              "$sort $order"
           ]
        ];

        if (Session::getLoginUserID()) {
            $criteria['WHERE'] += getEntitiesRestrictCriteria('glpi_documents', '', '', true);
        } else {
            // Anonymous access from FAQ
            $criteria['WHERE']['glpi_documents.entities_id'] = 0;
        }

        // Document : search links in both order using union
        $doc_criteria = [];
        if ($item->getType() == 'Document') {
            $owhere = $criteria['WHERE'];
            $o2where =  $owhere + ['glpi_documents_items.documents_id' => $item->getID()];
            unset($o2where['glpi_documents_items.items_id']);
            $criteria['WHERE'] = [
               'OR' => [
                  $owhere,
                  $o2where
               ]
            ];
        }

        $request = self::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();
        $number = count($results);
        $i      = 0;

        $documents = [];
        $used      = [];
        foreach ($results as $data) {
            $documents[$data['assocID']] = $data;
            $used[$data['id']]           = $data['id'];
        }

        $massiveActionContainerId = 'mass' . __CLASS__ . $params['rand'];
        if (
            $canedit
            && $number
            && ($withtemplate < 2)
        ) {
            $massiveactionparams = [
               'container'      => $massiveActionContainerId,
               'display_arrow' => false,
               'is_deleted' => 0,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           Entity::getTypeName(1),
           __('File'),
           __('Web link'),
           __('Heading'),
           __('MIME type'),
           __('Tag'),
           _n('Date', 'Dates', 1)
        ];
        $values = [];
        $massive_action = [];
        $document = new Document();
        foreach ($documents as $data) {
            $docID        = $data["id"];
            $link         = NOT_AVAILABLE;
            $downloadlink = NOT_AVAILABLE;

            if ($document->getFromDB($docID)) {
                $link         = $document->getLink();
                $downloadlink = $document->getDownloadLink($linkparam);
            }

            if ($item->getType() != 'Document') {
                Session::addToNavigateListItems('Document', $docID);
            }
            $used[$docID] = $docID;
            $assocID      = $data["assocID"];

            $values[] = [
               $link,
               $data['entity'],
               $downloadlink,
               !empty($data['link']) ? $data['link'] : NOT_AVAILABLE,
               $data['headings'],
               $data['mime'],
               !empty($data['tag']) ? Document::getImageTag($data['tag']) : '',
               Html::convDateTime($data['assocdate'])
            ];
            $massive_action[] = sprintf('item[%s][%s]', __CLASS__, $assocID);
        }
        renderTwigTemplate('table.twig', [
           'id' => $massiveActionContainerId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }


    public static function getRelationMassiveActionsPeerForSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'add':
            case 'remove':
                return 1;

            case 'add_item':
            case 'remove_item':
                return 2;
        }
        return 0;
    }


    public static function getRelationMassiveActionsSpecificities()
    {
        $specificities              = parent::getRelationMassiveActionsSpecificities();
        $specificities['itemtypes'] = Document::getItemtypesThatCanHave();

        // Define normalized action for add_item and remove_item
        $specificities['normalized']['add'][]          = 'add_item';
        $specificities['normalized']['remove'][]       = 'remove_item';

        // Set the labels for add_item and remove_item
        $specificities['button_labels']['add_item']    = $specificities['button_labels']['add'];
        $specificities['button_labels']['remove_item'] = $specificities['button_labels']['remove'];

        return $specificities;
    }

    /**
     * Get items for an itemtype
     *
     * @since 9.3.1
     *
     * @param integer $items_id Object id to restrict on
     * @param string  $itemtype Type for items to retrieve
     * @param boolean $noent    Flag to not compute enitty information (see Document_Item::getTypeItemsQueryParams)
     * @param array   $where    Inital WHERE clause. Defaults to []
     *
     * @return DBmysqlIterator
     */
    protected static function getTypeItemsQueryParams($items_id, $itemtype, $noent = false, $where = [])
    {
        $commonwhere = ['OR'  => [
           static::getTable() . '.' . static::$items_id_1  => $items_id,
           [
              static::getTable() . '.itemtype'                => static::$itemtype_1,
              static::getTable() . '.' . static::$items_id_2  => $items_id
           ]
        ]];

        if ($itemtype != 'KnowbaseItem') {
            $params = parent::getTypeItemsQueryParams($items_id, $itemtype, $noent, $commonwhere);
        } else {
            //KnowbaseItem case: no entity restriction, we'll manage it here
            $params = parent::getTypeItemsQueryParams($items_id, $itemtype, true, $commonwhere);
            $params['SELECT'][] = new QueryExpression('-1 AS entity');
            $kb_params = KnowbaseItem::getVisibilityCriteria();

            if (!Session::getLoginUserID()) {
                // Anonymous access
                $kb_params['WHERE'] = [
                   'glpi_entities_knowbaseitems.entities_id'    => 0,
                   'glpi_entities_knowbaseitems.is_recursive'   => 1
                ];
            }

            $params = array_merge_recursive($params, $kb_params);
        }

        return $params;
    }

    /**
     * Get linked items list for specified item
     *
     * @since 9.3.1
     *
     * @param CommonDBTM $item  Item instance
     * @param boolean    $noent Flag to not compute entity information (see Document_Item::getTypeItemsQueryParams)
     *
     * @return array
     */
    protected static function getListForItemParams(CommonDBTM $item, $noent = false)
    {

        if (Session::getLoginUserID()) {
            $params = parent::getListForItemParams($item);
        } else {
            $params = parent::getListForItemParams($item, true);
            // Anonymous access from FAQ
            $params['WHERE'][self::getTable() . '.entities_id'] = 0;
        }

        return $params;
    }

    /**
     * Get distinct item types query parameters
     *
     * @since 9.3.1
     *
     * @param integer $items_id    Object id to restrict on
     * @param array   $extra_where Extra where clause
     *
     * @return array
     */
    public static function getDistinctTypesParams($items_id, $extra_where = [])
    {
        $commonwhere = ['OR'  => [
           static::getTable() . '.' . static::$items_id_1  => $items_id,
           [
              static::getTable() . '.itemtype'                => static::$itemtype_1,
              static::getTable() . '.' . static::$items_id_2  => $items_id
           ]
        ]];

        $params = parent::getDistinctTypesParams($items_id, $extra_where);
        $params['WHERE'] = $commonwhere;
        if (count($extra_where)) {
            $params['WHERE'][] = $extra_where;
        }

        return $params;
    }

    /**
     * Check if this item author is a support agent
     *
     * @return bool
     */
    public function isFromSupportAgent()
    {
        // If not a CommonITILObject
        if (!is_a($this->fields['itemtype'], 'CommonITILObject', true)) {
            return true;
        }

        // Get parent item
        $commonITILObject = new $this->fields['itemtype']();
        $commonITILObject->getFromDB($this->fields['items_id']);

        $actors = $commonITILObject->getITILActors();
        $user_id = $this->fields['users_id'];
        $roles = $actors[$user_id] ?? [];

        if (in_array(CommonITILActor::ASSIGN, $roles)) {
            // The author is assigned -> support agent
            return true;
        } elseif (
            in_array(CommonITILActor::OBSERVER, $roles)
            || in_array(CommonITILActor::REQUESTER, $roles)
        ) {
            // The author is an observer or a requester -> not a support agent
            return false;
        } else {
            // The author is not an actor of the ticket -> he was most likely a
            // support agent that is no longer assigned to the ticket
            return true;
        }
    }
}
