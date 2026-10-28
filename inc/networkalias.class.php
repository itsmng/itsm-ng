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
* */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 *  NetworkAlias Class
 *
 * @since 0.84
**
 */
class NetworkAlias extends FQDNLabel
{
    // From CommonDBChild
    public static $itemtype           = 'NetworkName';
    public static $items_id           = 'networknames_id';
    public $dohistory                 = true;

    public static $checkParentRights = CommonDBConnexity::HAVE_SAME_RIGHT_ON_ITEM;


    public static function getTypeName($nb = 0)
    {
        return _n('Network alias', 'Network aliases', $nb);
    }


    public function defineTabs($options = [])
    {

        $ong  = [];
        $this->addDefaultFormTab($ong);

        return $ong;
    }


    /**
     * Get the full name (internet name) of a NetworkName
     *
     * @param integer $ID  ID of the NetworkName
     *
     * @return string  its internet name, or empty string if invalid NetworkName
    **/
    public static function getInternetNameFromID($ID)
    {

        $networkAlias = new self();
        if ($networkAlias->can($ID, READ)) {
            return FQDNLabel::getInternetNameFromLabelAndDomainID(
                $networkAlias->fields["name"],
                $networkAlias->fields["fqdns_id"]
            );
        }
        return "";
    }


    /**
     * Print the network alias form
     *
     * @param $ID        integer ID of the item
     * @param $options   array
     *     - target for the Form
     *     - withtemplate template or basic computer
     *
     * @return void
    **/
    public function showForm($ID, $options = [])
    {
        if (isset($options['parent'])) {
            $options['networknames_id'] = $options['parent']->getID();
        }
        $this->initForm($ID, $options);
        $parents = $this->recursivelyGetItems();
        if (!$parents) {
            return false;
        }
        $parent = $parents[count($parents) - 1];
        ob_start();
        $this->displayRecursiveItems($parents, 'Type');
        $this->displayRecursiveItems($parents, 'Link');
        $parent_context = ob_get_clean();
        renderTwigForm([
            'action' => self::getFormURL(),
            'buttons' => [[
                'type' => 'submit', 'name' => $this->isNewID($ID) ? 'add' : 'update',
                'value' => $this->isNewID($ID) ? _x('button', 'Add') : _x('button', 'Save'),
                'class' => 'btn btn-primary', 'icon' => $this->isNewID($ID) ? 'fas fa-plus' : null,
            ]],
            'content' => [self::getTypeName(1) => [
                'visible' => true,
                'inputs' => [
                    __('Linked to') => ['content' => $parent_context],
                    ['type' => 'hidden', 'name' => 'networknames_id', 'value' => $this->fields['networknames_id']],
                    $this->isNewID($ID) ? [] : ['type' => 'hidden', 'name' => 'id', 'value' => $ID],
                    __('Name') => ['type' => 'text', 'name' => 'name', 'value' => $this->fields['name']],
                    FQDN::getTypeName(1) => [
                        'type' => 'select', 'name' => 'fqdns_id', 'value' => $this->fields['fqdns_id'],
                        ...getAjaxDropdownOptionsByEntity(FQDN::class, $parent->getEntityID()),
                    ],
                    __('Comments') => ['type' => 'textarea', 'name' => 'comment', 'value' => $this->fields['comment']],
                ],
            ]],
        ]);
        return true;
    }


    /**
     * @since 0.84
     *
     * @param $itemtype
     * @param $base                  HTMLTableBase object
     * @param $super                 HTMLTableSuperHeader object (default NULL)
     * @param $father                HTMLTableHeader object (default NULL)
     * @param $options      array
    **/
    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        ?HTMLTableSuperHeader $super = null,
        ?HTMLTableHeader $father = null,
        array $options = []
    ) {

        $column_name = __CLASS__;
        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        if ($itemtype != 'NetworkName') {
            return;
        }

        $content = self::getTypeName();
        if (isset($options['column_links'][$column_name])) {
            $content = "<a href='" . $options['column_links'][$column_name] . "'>$content</a>";
        }
        $this_header = $base->addHeader($column_name, $content, $super, $father);
        $this_header->setItemType('NetworkAlias');
    }


    /**
     * @since 0.84
     *
     * @param $row                HTMLTableRow object (default NULL)
     * @param $item               CommonDBTM object (default NULL)
     * @param $father             HTMLTableCell object (default NULL)
     * @param $options   array
    **/
    public static function getHTMLTableCellsForItem(
        ?HTMLTableRow $row = null,
        ?CommonDBTM $item = null,
        ?HTMLTableCell $father = null,
        array $options = []
    ) {
        global $DB;

        if (empty($item)) {
            if (empty($father)) {
                return;
            }
            $item = $father->getItem();
        }

        if ($item->getType() != 'NetworkName') {
            return;
        }

        $column_name = __CLASS__;
        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        $header = $row->getGroup()->getHeaderByName('Internet', $column_name);
        if (!$header) {
            return;
        }

        $createRow            = (isset($options['createRow']) && $options['createRow']);
        $options['createRow'] = false;
        $alias                = new self();

        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => 'glpi_networkaliases',
           'WHERE'  => ['networknames_id' => $item->getID()]
        ]);

        while ($line = $iterator->next()) {
            if ($alias->getFromDB($line["id"])) {
                if ($createRow) {
                    $row = $row->createRow();
                }

                $content = "<a href='" . $alias->getLinkURL() . "'>" . $alias->getInternetName() . "</a>";
                $row->addCell($header, $content, $father, $alias);
            }
        }
    }


    /**
     * \brief Show aliases for an item from its form
     * Beware that the rendering can be different if readden from direct item form (ie : add new
     * NetworkAlias, remove, ...) or if readden from item of the item (for instance from the computer
     * form through NetworkPort::ShowForItem and NetworkName::ShowForItem).
     *
     * @param $item                     NetworkName object
     * @param $withtemplate   integer   withtemplate param (default 0)
    **/
    public static function showForNetworkName(NetworkName $item, $withtemplate = 0)
    {
        global $DB, $CFG_GLPI;

        $ID = $item->getID();
        if (!$item->can($ID, READ)) {
            return false;
        }

        $canedit = $item->canEdit($ID) && $withtemplate != 2;
        $rand    = mt_rand();

        $iterator = $DB->request([
           'FROM'   => 'glpi_networkaliases',
           'WHERE'  => ['networknames_id' => $ID]
        ]);
        $number = count($iterator);

        $aliases = [];
        while ($line = $iterator->next()) {
            $aliases[$line["id"]] = $line;
        }

        if ($canedit) {
            $alias = new self();
            $alias->showForm(-1, ['parent' => $item]);
        }
        $values = [];
        $selection = [];
        $alias = new self();
        foreach ($aliases as $data) {
            $alias->getFromResultSet($data);
            $values[] = [
                $alias->getLink(),
                htmlspecialchars(Dropdown::getDropdownName('glpi_fqdns', $data['fqdns_id']), ENT_QUOTES, 'UTF-8', false),
                htmlspecialchars(Dropdown::getDropdownName('glpi_entities', $data['entities_id']), ENT_QUOTES, 'UTF-8', false),
            ];
            $selection[] = sprintf('item[%s][%s]', self::class, $data['id']);
        }
        $table_id = 'networkAliases_' . $ID;
        $table = [
            'id' => $table_id,
            'state_key' => $table_id,
            'fields' => [__('Name'), FQDN::getTypeName(1), Entity::getTypeName(1)],
            'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ];
        if ($canedit) {
            Html::showMassiveActions([
                'container' => $table_id,
                'num_displayed' => count($values),
                'itemtype' => self::class,
                'display_arrow' => false,
            ]);
            $table['massive_action'] = $selection;
        }
        renderTwigTemplate('table.twig', $table);
    }


    /**
     * Show the aliases contained by the alias
     *
     * @param CommonGLPI $item          the FQDN owning the aliases
     * @param integer    $withtemplate  withtemplate param
    **/
    public static function showForFQDN(CommonGLPI $item, $withtemplate)
    {
        global $DB;
        $item->check($item->getID(), READ);
        $iterator = $DB->request([
            'SELECT' => ['glpi_networkaliases.id AS alias_id', 'glpi_networkaliases.name AS alias',
                'glpi_networknames.id AS address_id', 'glpi_networkaliases.comment AS comment'],
            'FROM' => self::getTable(),
            'INNER JOIN' => ['glpi_networknames' => ['ON' => [
                'glpi_networkaliases' => 'networknames_id', 'glpi_networknames' => 'id',
            ]]],
            'WHERE' => ['glpi_networkaliases.fqdns_id' => $item->getID()],
            'ORDERBY' => 'alias',
        ]);
        $address = new NetworkName();
        $values = [];
        foreach ($iterator as $data) {
            if (!$address->getFromDB($data['address_id'])) {
                continue;
            }
            $values[] = [
                '<a href="' . htmlspecialchars(self::getFormURLWithID($data['alias_id']), ENT_QUOTES, 'UTF-8', false) . '">'
                    . htmlspecialchars((string) $data['alias'], ENT_QUOTES, 'UTF-8', false) . '</a>',
                '<a href="' . htmlspecialchars((string) $address->getLinkURL(), ENT_QUOTES, 'UTF-8', false) . '">'
                    . htmlspecialchars((string) $address->getInternetName(), ENT_QUOTES, 'UTF-8', false) . '</a>',
                htmlspecialchars((string) $data['comment'], ENT_QUOTES, 'UTF-8', false),
            ];
            Session::addToNavigateListItems(self::class, $data['alias_id']);
        }
        renderTwigTemplate('table.twig', [
            'id' => 'fqdnAliases_' . $item->getID(), 'state_key' => 'fqdnAliases_' . $item->getID(),
            'fields' => [self::getTypeName(1), __("Computer's name"), __('Comments')],
            'values' => $values, 'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ]);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'NetworkName':
                self::showForNetworkName($item, $withtemplate);
                break;

            case 'FQDN':
                self::showForFQDN($item, $withtemplate);
                break;
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (
            $item->getID()
            && $item->can($item->getField('id'), READ)
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                switch ($item->getType()) {
                    case 'NetworkName':
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['networknames_id' => $item->getID() ]
                        );
                        break;

                    case 'FQDN':
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['fqdns_id' => $item->getID() ]
                        );
                }
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '12',
           'table'              => 'glpi_fqdns',
           'field'              => 'fqdn',
           'name'               => FQDN::getTypeName(1),
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '20',
           'table'              => 'glpi_networknames',
           'field'              => 'name',
           'name'               => NetworkName::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }
}
