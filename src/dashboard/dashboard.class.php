<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Dashboard extends \CommonDBTM
{
    public static $rightname = 'dashboard';

    /**
     * Return the title of the current dasbhoard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->fields['name'] ?? "";
    }

    public static function getMenuName()
    {
        return __('Dashboard');
    }

    public static function getIcon()
    {
        return 'fas fa-tachometer-alt';
    }

    /**
     * Show the form to create or edit a dashboard
     *
     * @param $ID: [profileId, userId]
     *
     * @return void
     */
    public function showForm($ID)
    {
        if ($ID) {
            $this->getFromDB($ID);
        }
        Html::requireJs('charts');

        $form = [
           'action' => $this->getFormURL(),
           'content' => [
              __('General') => [
                 'visible' => true,
                 'inputs' => [
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'] ?? '',
                       'required' => true,
                    ],
                    __('Profile') => [
                       'type' => 'select',
                       'id' => 'ProfileDropdownForDashboard',
                       'name' => 'profileId',
                       'value' => $this->fields['profileId'] ?? '',
                       'values' => getOptionForItems(Profile::class),
                       'required' => true,
                    ],
                    __('User') => [
                       'type' => 'select',
                       'id' => 'UserDropdownForDashboard',
                       'name' => 'userId',
                       'value' => $this->fields['userId'] ?? '',
                       'values' => getItemByEntity(User::class, Session::getActiveEntity()),
                       'required' => true,
                    ],
                 ]
              ],
              "Hidden" => [
                 'visible' => false,
                 'inputs' => [
                    [
                       'type' => 'hidden',
                       'name' => isset($ID) ? 'update' : 'add',
                       'value' => 'true',
                    ],
                    $this->isNewID($ID) ? [] : [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form);
        if ($ID) {
            $this->show($ID, true);
        }
    }

    public function getForUser()
    {
        $userId = (int) Session::getLoginUserID();

        try {
            $em = \Itsmng\Infrastructure\Persistence\EntityManagerProvider::getEntityManager();

            $repo = $em->getRepository(\Itsmng\Domain\Entities\Dashboard::class);

            $qb = $repo->createQueryBuilder('d')
                ->select('d.id')
                ->leftJoin('d.user', 'u')
                ->where('u.id = :uid')
                ->setParameter('uid', $userId)
                ->setMaxResults(1);

            $result = $qb->getQuery()->getScalarResult();

            // Fallback to the global dashboard (userId = 0) if none found
            if (empty($result)) {
                $qb = $repo->createQueryBuilder('d')
                    ->select('d.id')
                    ->where('IDENTITY(d.user) = 0')
                    ->setMaxResults(1);
                $result = $qb->getQuery()->getScalarResult();
            }

            if (empty($result)) {
                return false;
            }

            $this->getFromDB($result[0]['id']);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function show($ID = null, $edit = false)
    {
        global $CFG_GLPI;

        Html::requireJs('charts');
        $twig_vars = [];

        try {
            if ($edit) {
                $objects = $CFG_GLPI['globalsearch_types'];
                asort($objects);
                $values = [];
                foreach ($objects as $object) {
                    $values[$object] = ((string) $object)::getTypeName();
                }
                $jsUpdate = <<<JS
               $.ajax({
                  url: "{$CFG_GLPI['root_doc']}/src/dashboard/dashboard.ajax.php",
                  data: {
                     action: "getSearch",
                     itemtype: $('#ItemTypeDropdownForDashboard').val(),
                  },
                  success: function(data) {
                     $('#data-selection-search-content').html(data);
                     $('#data-selection-search-content form').attr('action', "#");
                     fetchPreview("{$CFG_GLPI['root_doc']}/src/dashboard/dashboard.ajax.php");
                  }
               });
            JS;
                ob_start();
                renderTwigTemplate('macros/wrappedInput.twig', [
                   'title' => __('Itemtype'),
                   'input' => [
                      'type' => 'select',
                      'id' => 'ItemTypeDropdownForDashboard',
                      'values' => $values,
                      'value' => array_key_first($values),
                      'col_lg' => 12,
                      'col_md' => 12,
                      'init' => $jsUpdate,
                      'hooks' => [
                         'change' => $jsUpdate,
                      ]
                   ]
                ]);
                $twig_vars['dataSelection'] = ob_get_clean();
            };
            $twig_vars['ajaxUrl'] = $CFG_GLPI['root_doc'] . "/src/dashboard/dashboard.ajax.php";
            $twig_vars['edit'] = $edit;
            $twig_vars['dashboardId'] = $ID ?? $this->fields['id'] ?? null;
            $twig_vars['widgetGrid'] = self::expandContent(json_decode($this->fields['content'] ?? '[]', true) ?? []);
            $twig_vars['base'] = $CFG_GLPI['root_doc'];
            renderTwigTemplate('dashboard/dashboard.twig', $twig_vars);
        } catch (Exception $e) {
            echo <<<HTML
         <div class="center b">
            {$e->getMessage()}
         </div>
         HTML;
        }
    }

    private static function expandContent($content)
    {
        // make series and labels from content
        foreach ($content as $rowKey => $row) {
            foreach ($row as $cellKey => $widget) {
                if (isset($widget['filters']['itemtype'])) {
                    $res = Search::getDatas($widget['filters']['itemtype'], $widget['filters']);
                    $content[$rowKey][$cellKey]['value'] = $res['data']['totalcount'];
                }
            }
        }
        return $content;
    }

    private function placeWidgetAtCoord(&$content, $widget, $coords)
    {
        [$x, $y] = $coords;
        if ($x == -1) {
            array_unshift($content, [$widget]);
        } elseif ($x > count($content)) {
            array_push($content, [$widget]);
        } else {
            if ($y == -1) {
                array_unshift($content[$x], $widget);
            } elseif ($y > count($content[$x])) {
                array_push($content[$x], $widget);
            } else {
                array_splice($content[$x], $y, 0, [$widget]);
            }
        }
    }

    public static function getWidgetUrl($type, $statType, $statSelection, $options = [])
    {
        $encodedSelection = urlencode($statSelection);
        $url = "/dashboard/$type?statType=$statType&statSelection=$encodedSelection";
        if ($type != 'count') {
            $comparison = $options['comparison'] ?? 'id';
            $url .= "&comparison={$comparison}";
        }
        return $url;
    }

    public function addWidget(array $coords = [0, 0], string $title = '', array $filters = [], string $icon = '', string $format = 'number')
    {
        $dashboard = json_decode($this->fields['content'], true) ?? [];
        $widget = [
           'title' => $title,
           'filters' => $filters,
           'icon' => $icon,
           'format' => $format,
        ];

        $this->placeWidgetAtCoord($dashboard, $widget, $coords);

        $content = str_replace("\\", "\\\\", json_encode($dashboard, JSON_UNESCAPED_UNICODE));
        if ($widget && $this->update(['id' => $this->fields['id'], 'content' => $content])) {
            Session::addMessageAfterRedirect(__("Widget added successfuly"));
        } else {
            Session::addMessageAfterRedirect(__("Widget could not be added"), false, ERROR);
        }
        Html::back();
    }

    public function deleteWidget($coords)
    {
        [$x, $y] = $coords;
        $dashboard = json_decode($this->fields['content'], true);
        array_splice($dashboard[$x], $y, 1);
        if (empty($dashboard[$x])) {
            array_splice($dashboard, $x, 1);
        }
        $content = str_replace("\\", "\\\\", json_encode($dashboard, JSON_UNESCAPED_UNICODE));

        return ($this->update(['id' => $this->fields['id'], 'content' => $content]));
    }
}
