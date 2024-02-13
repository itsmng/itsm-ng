<?php

/**
 * @param $form
 */
function getOptionForItems($item, $conditions = [], $display_emptychoice = true, $isDevice = false, $used = [])
{
    global $DB;

    $table = getTableForItemType($item);
$name = $isDevice ? 'designation' : 'name';
    $iterator = $DB->request([
        'SELECT' => ['id', $name ],
        'FROM' => $table,
        'WHERE' => $conditions,
    ]);

    $options = [];
    
    while ($val = $iterator->next()) {
                $options[$val['id']] = $val[$name] == '' ? '(' . $val['id'] . ')' : $val[$name];
            }
        if ($display_emptychoice) {
        if (!isset($options[0])) {
            $options = [0 => Dropdown::EMPTY_VALUE] + $options;
    } else {
        $options = [-1 => Dropdown::EMPTY_VALUE] + $options;
        }
    }
foreach ($used as $id) {
        unset($options[$id]);
    }

    return $options;
}

function getLinkedDocumentsForItem($itemType, $items_id) {
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
        $options[$val['id']] = "<a href=".$document->getFormURLWithID($val['documents_id'])
            .">".$document->fields['filename']." (".filesize(GLPI_DOC_DIR . $document->fields['filepath'])."B)</a>";
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

function renderTwigTemplate($path, $vars)
{
    global $CFG_GLPI;
    require_once GLPI_ROOT . '/src/twig/twig.class.php';
    $twig = Twig::load(GLPI_ROOT . '/templates', false);
    if (!isset($vars['root_doc'])) {
        $vars['root_doc'] = $CFG_GLPI['root_doc'];
    }
    try {
        echo $twig->render($path, $vars);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @return string
 */
function renderTwigForm($form, $additionnalHtml = '', $fields = [])
{
    global $CFG_GLPI;

    $twig = Twig::load(GLPI_ROOT . '/templates', false);
    if (isset($fields['id']) && $fields['id'] > 0) {
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
                'value' => $fields['template_name']
            ]
        ], $form['content'][array_key_first($form['content'])]['inputs']);
    };
    if (count($_SESSION['glpiactiveentities']) > 1) {
        $form['content'] = [Entity::getTypeName() => [
            'visible' => true,
            'inputs' => [
                __('Entity') => [
                    'type' => 'select',
                    'name' => 'entities_id',
                    'values' => getOptionForItems(Entity::class),
                    'value' => $fields['entities_id'] ?? Session::getActiveEntity(),
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
    } else {
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
    try {
        echo $twig->render('form.twig', [
            'form' => $form,
            'additionnalHtml' => $additionnalHtml,
            'root_doc' => $CFG_GLPI['root_doc'],
            'csrf_token' => $_SESSION['_glpi_csrf_token'],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function getItemActionButtons(array $actions, string $itemType): array
{
    $buttons = [];

    foreach ($actions as $action) {
        $content = [];
        switch ($action) {
            case 'info':
                $item = new $itemType;
                $itemSearchUrl = $item->getSearchUrl();
                $content = [
                    'icon' => 'fas fa-info',
                    'onClick' => "window.location.href = '{$itemSearchUrl}'",
                ];
                break;
            case 'add':
                $item = new $itemType;
                $itemFormUrl = $item->getFormUrl();
                $content = [
                    'icon' => 'fas fa-plus',
                    'onClick' => "window.location.href = '{$itemFormUrl}'",
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
