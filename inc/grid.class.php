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

use Ramsey\Uuid\Uuid;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Grid extends CommonGLPI
{
    protected $cell_margin     = 6;
    protected $grid_cols       = 12;
    protected $grid_rows       = 24;
    protected $current         = "";
    protected $dashboard       = null;
    protected $items           = [];

    public static $embed              = false;
    public static $context            = '';
    public static $all_dashboards     = [];

    public function __construct(
        string $dashboard_key = "central",
        int $grid_cols = 12,
        int $grid_rows = 24,
        string $context = "core"
    ) {

        $this->current   = $dashboard_key;
        $this->grid_cols = $grid_cols;
        $this->grid_rows = $grid_rows;

        $this->dashboard = new Dashboard($dashboard_key);
        self::$context   = $context;
    }


    /**
     * Return the instance of current dasbhoard
     *
     * @return Dashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * Do we have the right to view at least one dashboard int the current collection
     *
     * @return bool
     */
    public function canViewCurrent(): bool
    {
        // check global (admin) right
        if (Dashboard::canView()) {
            return true;
        }

        return $this->dashboard->canViewCurrent();
    }

    /**
     * Display grid for the current dashboard
     *
     * @return void display html of the grid
     */
    public function show($content)
    {
        global $CFG_GLPI;
        Html::requireJs('charts');
        $content['data_types'] = $CFG_GLPI['globalsearch_types'];
        $content['dashboardId'] = $this->current;
        $content['addWidget_action'] = Dashboard::getFormURL();
        try {
            renderTwigTemplate('dashboard/dashboard.twig', $content);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Show an embeded dashboard.
     * We must check token validity to avoid displaying dashboard to invalid users
     *
     * @param array $params contains theses keys:
     * - dashboard: the dashboard system name
     * - entities_id: entity to init in session
     * - is_recursive: do we need to display sub entities
     * - token: the token to check
     *
     * @return void (display)
     */
    public function embed(array $params = [])
    {
        $defaults = [
           'dashboard'    => '',
           'entities_id'  => 0,
           'is_recursive' => 0,
           'token'        => ''
        ];
        $params = array_merge($defaults, $params);

        if (!self::checkToken($params)) {
            Html::displayRightError();
            exit;
        }

        self::$embed = true;

        // load minimal session
        $_SESSION["glpiactive_entity"]           = $params['entities_id'];
        $_SESSION["glpiactive_entity_recursive"] = $params['is_recursive'];
        $_SESSION["glpiname"]                    = 'embed_dashboard';
        $_SESSION["glpigroups"]                  = [];
        if ($params['is_recursive']) {
            $entities = getSonsOf("glpi_entities", $params['entities_id']);
        } else {
            $entities = [$params['entities_id']];
        }
        $_SESSION['glpiactiveentities']        = $entities;
        $_SESSION['glpiactiveentities_string'] = "'" . implode("', '", $entities) . "'";

        // show embeded dashboard
        $this->show(true);
    }

    public static function getToken(string $dasboard = "", int $entities_id = 0, int $is_recursive = 0): string
    {
        $seed         = $dasboard . $entities_id . $is_recursive . Telemetry::getInstanceUuid();
        $uuid         = Uuid::uuid5(Uuid::NAMESPACE_OID, $seed);
        $token        = $uuid->toString();

        return $token;
    }

    /**
     * Check token variables (compare it to `dashboard`, `entities_id` and `is_recursive` paramater)
     *
     * @param array $params contains theses keys:
     * - dashboard: the dashboard system name
     * - entities_id: entity to init in session
     * - is_recursive: do we need to display sub entities
     * - token: the token to check
     *
     * @return bool
     */
    public static function checkToken(array $params = []): bool
    {
        $defaults = [
           'dashboard'    => '',
           'entities_id'  => 0,
           'is_recursive' => 0,
           'token'        => ''
        ];
        $params = array_merge($defaults, $params);

        $token = self::getToken(
            $params['dashboard'],
            $params['entities_id'],
            $params['is_recursive']
        );

        if ($token !== $params['token']) {
            return false;
            Html::displayRightError();
            exit;
        }

        return true;
    }

    /**
     * Add a new grid item
     *
     * @param string $html content of the card
     * @param string $gridstack_id unique id identifying the card (used in gridstack)
     * @param int $x position in the grid
     * @param int $y position in the grid
     * @param int $width size in the grid
     * @param int $height size in the grid
     * @param array $data_option aditional options passed to the widget, contains at least thses keys:
     *                             - string 'color'
     * @return void
     */
    public function addGridItem(
        string $html = "",
        string $gridstack_id = "",
        int $x = -1,
        int $y = -1,
        int $width = 2,
        int $height = 2,
        array $data_option = []
    ) {

        // let grid-stack to autoposition item
        $autoposition = 'gs-auto-position="true"';
        $coordinates  = '';
        if ((int) $x >= 0 && (int) $y >= 0) {
            $autoposition = "";
            $coordinates  = "gs-x='$x' gs-y='$y'";
        }

        $color    = $data_option['color'] ?? "#FFFFFF";
        $fg_color = Toolbox::getFgColor($color, 100);

        // add card options in data attribute
        $data_option_attr = "";
        if (count($data_option)) {
            $data_option_attr = "data-card-options='" . json_encode($data_option, JSON_HEX_APOS) . "'";
        }

        $refresh_label = __("Refresh this card");
        $edit_label    = __("Edit this card");
        $delete_label  = __("Delete this card");

        $gridstack_id = htmlspecialchars($gridstack_id);

        $this->items[] = <<<HTML
         <div class="grid-stack-item"
               gs-id="{$gridstack_id}"
               gs-w="{$width}"
               gs-h="{$height}"
               {$coordinates}
               {$autoposition}
               {$data_option_attr}
               style="color: {$fg_color}">
            <span class="controls">
               <i class="refresh-item fas fa-sync-alt" title="{$refresh_label}"></i>
               <i class="edit-item fas fa-edit" title="{$edit_label}"></i>
               <i class="delete-item fas fa-times" title="{$delete_label}"></i>
            </span>
            <div class="grid-stack-item-content">{$html}</div>
         </div>
HTML;
    }

    /**
     * Return Html for a provided set of filters
     * @param array $filter_names
     *
     * @return string the html
     */
    public function getFiltersSetHtml(array $filters = []): string
    {
        $html = "";

        foreach ($filters as $filter_id => $filter_values) {
            $html .= $this->getFilterHtml($filter_id, $filter_values);
        }

        return $html;
    }


    /**
     * Return Html for a provided filter name
     *
     * @param string $filter_id the system name of a filter (ex dates)
     * @param string|array $filter_values init the input with these values,
     *                     will be a string if empty values
     *
     * @return string the html
     */
    public function getFilterHtml(string $filter_id = "", $filter_values = ""): string
    {
        if (method_exists("Glpi\Dashboard\Filter", $filter_id)) {
            return call_user_func("Glpi\Dashboard\Filter::$filter_id", $filter_values);
        }

        return "";
    }


    /**
     * Return all itemtypes possible for constructing cards.
     * User in @see self::getAllDasboardCards
     *
     * @return array [itemtype1, itemtype2]
     */
    protected function getMenuItemtypes(): array
    {
        $menu_itemtypes = [];
        $exclude   = [
           'Config',
        ];

        $menu = Html::getMenuInfos();
        array_walk($menu, static function ($firstlvl) use (&$menu_itemtypes): void {
            $key = $firstlvl['title'];
            if (isset($firstlvl['types'])) {
                $menu_itemtypes[$key] = array_merge($menu_itemtypes[$key] ?? [], $firstlvl['types']);
            }
        });

        foreach ($menu_itemtypes as &$firstlvl) {
            $firstlvl = array_filter($firstlvl, static function ($itemtype) use ($exclude) {
                if (
                    in_array($itemtype, $exclude)
                    || !is_subclass_of($itemtype, 'CommonDBTM')
                ) {
                    return false;
                }

                $testClass = new \ReflectionClass($itemtype);
                return !$testClass->isAbstract();
            });
        }

        return $menu_itemtypes;
    }

    public function getRights($interface = 'central')
    {
        return [
           READ   => __('Read'),
           UPDATE => __('Update'),
           CREATE => __('Create'),
           PURGE  => [
              'short' => __('Purge'),
              'long'  => _x('button', 'Delete permanently')
           ]
        ];
    }


    /**
     * Save last dashboard viewed
     *
     * @param string $page current page
     * @param string $dashboard current dashboard
     *
     * @return void
     */
    public function setLastDashboard(string $page = "", string $dashboard = "")
    {
        $_SESSION['last_dashboards'][$page] = $dashboard;
    }


    /**
     * Restore last viewed dashboard
     *
     * @return string the dashboard key
     */
    public function restoreLastDashboard(): string
    {
        global $CFG_GLPI;
        $new_key = "";
        $target = Toolbox::cleanTarget($_REQUEST['_target'] ?? $_SERVER['REQUEST_URI'] ?? "");
        if (isset($_SESSION['last_dashboards']) && strlen($target) > 0) {
            $target = preg_replace('/^' . preg_quote($CFG_GLPI['root_doc'], '/') . '/', '', $target);
            if (!isset($_SESSION['last_dashboards'][$target])) {
                return "";
            }

            $new_key   = $_SESSION['last_dashboards'][$target];
            $dashboard = new Dashboard($new_key);
            if (!$dashboard->canViewCurrent()) {
                return "";
            }

            $this->current = $new_key;
        }

        return $new_key;
    }
}
