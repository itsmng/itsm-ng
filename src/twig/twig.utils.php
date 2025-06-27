<?php

function expandSelect(&$select, $fields = [])
{
    global $CFG_GLPI;

    if (isset($select['itemtype']) && !isset($select['values'])) {
        $restrict = $select['condition']['entities_id'] ?? $fields['entities_id'] ?? (Session::getActiveEntity() == 0 ? -1 : Session::getActiveEntity());
        $recursive = $select['condition']['is_recursive'] ?? $fields['is_recursive'] ?? Session::getIsActiveEntityRecursive();
        if (isset($select['condition']['entities_id'])) {
            unset($select['condition']['entities_id']);
        }
        if (isset($select['condition']['is_recursive'])) {
            unset($select['condition']['is_recursive']);
        }
        $select['values'] = ($select['display_emptychoice'] ?? true ? [DROPDOWN::EMPTY_VALUE] : []) +
        getItemByEntity(
            $select['itemtype'],
            $restrict,
            $select['condition'] ?? [],
            $select['used'] ?? []
        );
        if (isset($select['value']) && !in_array($select['value'], $select['values'])) {
            $item = new $select['itemtype']();
            $item->getFromDB($select['value']);
            if (isset($item->fields['name'])) {
                $select['values'][$select['value']] = $item->fields['name'];
            }
        }
        $select['ajax'] =
            [
                'url' => $CFG_GLPI['root_doc'] . '/ajax/getDropdownValue.php',
                'type' => "POST",
                'data' => [
                    'itemtype' => $select['itemtype'],
                    'display_emptychoice' => $select['display_emptychoice'] ?? 1,
                    'condition' => $select['condition'] ?? [],
                    'permit_parent_select' => 0,
                    'entity_restrict' => $restrict,
                    'recursive' => $recursive,
                    'used' => $select['used'] ?? [],
                    'emptylabel' => Dropdown::EMPTY_VALUE,
                    'permit_select_parent' => 0,
                ],
            ];
    }
    return $select;
}

function expandForm($form, $fields = [], $template = null)
{
    foreach ($form['content'] as $contentKey => $content) {
        if (isset($content['inputs'])) {
            $filteredInputs = [];
            
            foreach ($content['inputs'] as $inputKey => $input) {
                $shouldHide = false;
                
                if (isset($input['name']) && isset($template) && $template->isHiddenField($input['name'])) {
                    $shouldHide = true;
                }
                
                if (strpos(strtolower($inputKey), 'sla') !== false && 
                    (strpos(strtolower($inputKey), 'time') !== false || 
                     strpos(strtolower($inputKey), 'own') !== false || 
                     strpos(strtolower($inputKey), 'resolve') !== false)) {
                    $shouldHide = true;
                }
                
                if ($shouldHide) {
                    continue;
                }
                
                $filteredInputs[$inputKey] = $input;
                
                if ($input['type'] ?? '' == 'select') {
                    expandSelect($filteredInputs[$inputKey], $fields);
                }
            }
            
            $form['content'][$contentKey]['inputs'] = $filteredInputs;
        }
    }
    if (!isset($form['buttons']) && isset($form['itemtype'])) {
        $item = new $form['itemtype']();
        $isNew = !isset($fields['id']) || intval($fields['id']) <= 0 ||
            isset($fields['withtemplate']) && $fields['withtemplate'] == 2;
        $isDeleted = isset($fields['is_deleted']) && $fields['is_deleted'];

        $form['buttons'] = [
            $isNew ? ($item::canCreate() ? [
                'class' => 'btn btn-secondary',
                'name' => 'add',
                'value' => __('Add'),
            ] : []) : ($item::canUpdate() ? [
                'class' => 'btn btn-secondary',
                'name' => 'update',
                'value' => __('Update'),
            ] : []),
            !$isNew && ($isDeleted || !isset($fields['is_deleted'])) ? ($item::canPurge() ? [
                'class' => 'btn btn-danger',
                'name' => 'purge',
                'value' => __('Delete permanently'),
            ] : []) : ($item::canDelete() && !$isNew ? [
                'class' => 'btn btn-danger',
                'name' => 'delete',
                'value' => __('Put in trashbin'),
            ] : []),
            $isDeleted && $item::canDelete() ? [
                'class' => 'btn btn-success',
                'name' => 'restore',
                'value' => __('Restore'),
            ] : [],
        ];
    }
    return $form;
}

function getItemByEntity($itemtype, $entity, $conditions = [], $used = [])
{
    $cond = $conditions;
    if (isset($conditions['entities_id'])) {
        $cond = $conditions + getEntitiesRestrictCriteria(
            $itemtype::getTable(),
            'entities_id',
            $entity,
            true
        );
    }
    $key = Dropdown::addNewCondition($cond);
    $values = Dropdown::getDropdownValue([
        'itemtype' => $itemtype,
        'condition' => $key,
        'used' => $used,
    ], false);
    $options = [];
    foreach ($values['results'] as $key => $value) {
        if (!$value || !count($value)) {
            continue;
        }

        if (isset($value['children'])) {
            if (!isset($options[$value['text']])) {
                $options[$value['text']] = [];
            }
            foreach ($value['children'] as $childValue) {
                $options[$value['text']][$childValue['id']] = $childValue['text'];
            }
        } else {
            $options[$value['id']] = $value['text'];
        }
    }
    return $options;
}

function getOptionForItems($item, $conditions = [], $display_emptychoice = true, $isDevice = false, $used = [])
{
    $entity_restrict = false;
    if (isset($conditions['entities_id']) && isset($conditions['is_recursive'])) {
        $entity_restrict = '[' .
            '"entities_id": ' . $conditions['entities_id'] . ',' .
            '"is_recursive": ' . $conditions['is_recursive'] . ']';
    }
    $values = Dropdown::getDropdownValue([
        'itemtype' => $item,
        'entity_restrict' => $entity_restrict ?? -1,
        'condition' => $conditions,
        'used' => $used,
        'display_emptychoice' => $display_emptychoice,
    ], false);

    $options = [];
    foreach ($values['results'] as $key => $value) {
        if (!$value || !count($value)) {
            continue;
        }

        if (isset($value['children'])) {
            $options[$value['text']] = [];
            foreach ($value['children'] as $childValue) {
                $options[$value['text']][$childValue['id']] = $childValue['text'];
            }
        } else {
            $options[$value['id']] = $value['text'];
        }
    }
    return $options;
}

function getLinkedDocumentsForItem($itemType, $items_id)
{
    global $DB;

    $iterator = $DB->request([
        'SELECT' => ['id', 'documents_id'],
        'FROM' => Document_Item::getTable(),
        'WHERE' => [
            'itemType' => $itemType,
            'items_id' => $items_id,
        ],
    ]);

    $options = [];
    $document = new Document();
    while ($val = $iterator->next()) {
        $document->getFromDB($val['documents_id']);
        $options[$val['id']] = "<a href=" . $document->getFormURLWithID($val['documents_id'])
            . ">" . $document->fields['filename'] . " (" . filesize(GLPI_DOC_DIR . $document->fields['filepath']) . "B)</a>";
    }

    return $options;
}

function getOptionsForUsers($right, $conditions = [], $display_emptychoice = true)
{

    $rights = $right;
    if (gettype($right) != 'array') {
        $rights = [$right];
    }
    $users = [];
    foreach ($rights as $right) {
        if (isset($conditions['entities_id'])) {
            $users += iterator_to_array(User::getSqlSearchResult(false, $right, $conditions['entities_id']));
        } else {
            $users += iterator_to_array(User::getSqlSearchResult(false, $right));
        }
    }
    $options = [];
    if ($display_emptychoice) {
        $options[0] = Dropdown::EMPTY_VALUE;
    }
    foreach ($users as $user) {
        $options[$user['id']] = $user['name'];
    }

    return $options;
}

function renderTwigTemplate($path, $vars, $root = '/templates')
{
    global $CFG_GLPI;
    require_once GLPI_ROOT . '/src/twig/twig.class.php';
    $twig = Twig::load(GLPI_ROOT . $root, false);
    if (!isset($vars['root_doc'])) {
        $vars['root_doc'] = $CFG_GLPI['root_doc'];
    }
    try {
        echo $twig->render($path, $vars);
    } catch (Exception $e) {
        echo "<div class='text-start'>";
        echo $e->getMessage();
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
        echo $e->getFile() . ':' . $e->getLine();
        echo "</div>";
    }
}

/**
 * @return string
 */
function renderTwigForm($form, $additionnalHtml = '', $fields = [], $template = null)
{
    global $CFG_GLPI;

    $twig = Twig::load(GLPI_ROOT . '/templates', false);
    if (isset($fields['id']) && $fields['id'] > 0 && !isset($fields['noId'])) {
        $form['content'][array_key_first($form['content'])]['inputs'] = array_merge([
            [
                'type' => 'hidden',
                'name' => 'id',
                'value' => $fields['id'],
            ],
        ], $form['content'][array_key_first($form['content'])]['inputs']);
    }
    if (isset($_GET['withtemplate']) && $_GET['withtemplate'] == 1) {
        $form['content'][array_key_first($form['content'])]['inputs'] = array_merge([
            [
                'type' => 'hidden',
                'name' => 'is_template',
                'value' => 1,
            ],
            __('Template name') => [
                'type' => 'text',
                'name' => 'template_name',
                'value' => $fields['template_name'] ?? ''
            ]
        ], $form['content'][array_key_first($form['content'])]['inputs']);
    };
    if (
        isset($_SESSION['glpiactiveentities']) &&
        count($_SESSION['glpiactiveentities']) >= 1 &&
        isset($fields['entities_id']) && !isset($fields['noEntity'])
    ) {
        $entity_name = Dropdown::getDropdownName('glpi_entities', $fields['entities_id']);
        $form['content'] = [Entity::getTypeName() => [
            'visible' => true,
            'inputs' => [
                [
                    'type' => 'hidden',
                    'name' => 'entities_id',
                    'value' => $fields['entities_id'],
                ],
                __('Entity') => [
                    'content' => $entity_name,
                    'col_lg' => 8,
                    'col_md' => 8,
                ],
                __('Recursive') => [
                    'type' => 'checkbox',
                    'name' => 'is_recursive',
                    'value' => $fields['is_recursive'] ?? Session::getIsActiveEntityRecursive(),
                ],
            ],
        ]] + $form['content'];
    } elseif (isset($fields['entities_id']) && !isset($fields['noEntity'])) {
        $form['content'][array_key_first($form['content'])]['inputs'] = array_merge([
            [
                'type' => 'hidden',
                'name' => 'entities_id',
                'value' => Session::getActiveEntity(),
            ],
            [
                'type' => 'hidden',
                'name' => 'is_recursive',
                'value' => Session::getIsActiveEntityRecursive(),
            ],
        ], $form['content'][array_key_first($form['content'])]['inputs']);
    }
    if (isset($form['itemtype'])) {
        $item = new $form['itemtype']();
        if (isset($fields['id']) && $fields['id'] > 0) {
            $item->getFromDB($fields['id']);
        }
        $preItemFormHtml = '';
        ob_start();
        Plugin::doHook("pre_item_form", ['item' => $item]);
        if (!empty($additionnalHtml)) {
            $preItemFormHtml .= ob_get_clean();
        }
        ob_start();
        Plugin::doHook("post_item_form", ['item' => $item]);
        if (!empty($additionnalHtml)) {
            $additionnalHtml .= ob_get_clean();
        } else {
            $additionnalHtml = ob_get_clean();
        }
    }
    try {
        echo $twig->render('form.twig', [
            'form' => expandForm($form, $fields, $template),
            'preItemFormHtml' => $preItemFormHtml ?? '',
            'additionnalHtml' => $additionnalHtml,
            'root_doc' => $CFG_GLPI['root_doc'],
            'csrf_token' => $_SESSION['_glpi_csrf_token'],
        ]);
    } catch (Exception $e) {
        echo "<div class='text-start'>";
        echo $e->getMessage();
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
        echo $e->getFile() . ':' . $e->getLine();
        echo "</div>";
    }
}

function renderTwigPage($title, $content, $sector = 'none', $item = 'none', $option = '')
{
    global $CFG_GLPI;

    $mainMenu = Html::getMainMenu($sector, $item, $option);
    $menu = $mainMenu['args']['menu'];
    $breadcrumb_items = [
        [
            'title' => __('Home'),
            'href'  => $CFG_GLPI['root_doc'] . '/front/central.php'
        ],
    ];
    if (isset($sector) && isset($menu[$sector])) {
        $breadcrumb_items[] = [
            'title' => $menu[$sector]['title'],
            'href'  => $CFG_GLPI['root_doc'] . $menu[$sector]['default']
        ];
    };
    if (isset($sector) && isset($menu[$sector]) && isset($menu[$sector]['content'][$item])) {
        $breadcrumb_items[] = [
            'title' => $menu[$sector]['content'][$item]['title'],
            'href'  => $CFG_GLPI['root_doc'] . $menu[$sector]['content'][$item]['page'],
        ];
    };

    ob_start();
    Html::showProfileSelecter($CFG_GLPI["root_doc"] . "/front/"
        . (Session::getCurrentInterface() == 'central' ? 'central' : 'helpdesk.public') . ".php");
    $profileSelect = ob_get_clean();

    $impersonate_banner = Html::getImpersonateBanner();

    $twig_vars = [
        'title' => $title,
        'mainContent' => $content,
        'main_menu' => Html::getMainMenu($sector, $item, $option),
        'css' => Html::getCss(),
        'js' => Html::getJs(),
        'breadcrumb_items' => $breadcrumb_items,
        'profileSelect' => $profileSelect,
        'is_debug_active' => $_SESSION['glpi_use_mode'] == Session::DEBUG_MODE,
        'can_update' => Config::canUpdate(),
        'username' => getUserName(Session::getLoginUserID()),
    ];
    if ($impersonate_banner) {
        $twig_vars['impersonate_banner'] = $impersonate_banner;
    }

    renderTwigTemplate('base/base.twig', $twig_vars);
}

function getItemActionButtons(array $actions, string $itemType): array
{
    $buttons = [];

    foreach ($actions as $action) {
        $content = [];
        switch ($action) {
            case 'info':
                $item = new $itemType();
                $itemSearchUrl = $item->getSearchUrl();
                $content = [
                    'icon' => 'fas fa-info',
                    'onClick' => "window.location.href = '{$itemSearchUrl}'",
                    'info' => 'Info',
                ];
                break;
            case 'add':
                $item = new $itemType();
                Ajax::createModalWindow('add_' . $itemType, $item->getFormUrl() . '?_in_modal=1');
                $content = [
                    'icon' => 'fas fa-plus',
                    'onClick' => "add_" . $itemType . ".dialog('open');",
                    'info' => 'Add',
                ];
                break;
        }
        $buttons[$action] = $content;
    }

    return $buttons;
}

function getOptionsWithNameForItem(string $itemType, array $conditions, array $names): array
{
    global $DB;

    $table = getTableForItemType($itemType);
    $iterator = $DB->request([
        'SELECT' => array_merge(['id'], array_values($names)),
        'FROM' => $table,
        'WHERE' => $conditions,
    ]);

    $options = [];
    while ($val = $iterator->next()) {
        $newItem = ['id' => $val['id']];
        foreach ($names as $key => $name) {
            $newItem[$key] = $val[$name];
        }
        $options[] = $newItem;
    }

    return $options;
}
