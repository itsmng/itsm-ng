<?php

/**
 * @param $form
 */
function getOptionForItems($item, $conditions = [], $display_emptychoice = true)
{
    global $DB;

    $table = getTableForItemType($item);
    $iterator = $DB->request([
        'SELECT' => ['id', 'name'],
        'FROM' => $table,
        'WHERE' => $conditions,
    ]);

    $options = [];
    if ($display_emptychoice) {
        $options[0] = Dropdown::EMPTY_VALUE;
    }
    while ($val = $iterator->next()) {
        $options[$val['id']] = $val['name'];
    }

    return $options;
}

function getOptionsForUsers($right, $conditions = [], $display_emptychoice = true)
{

    if (isset($conditions['entities_id'])) {
      $users = iterator_to_array(User::getSqlSearchResult(false, $right, $conditions['entities_id']));
    } else {
      $users = iterator_to_array(User::getSqlSearchResult(false, $right));
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
    require_once GLPI_ROOT . '/src/twig/twig.class.php';
    $twig = Twig::load(GLPI_ROOT . '/templates', false);
    try {
        echo $twig->render($path, $vars);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @return string
 */
function renderTwigForm($form, $additionnalHtml = '', $colAmount = 2)
{
    global $CFG_GLPI;

    $twig = Twig::load(GLPI_ROOT . '/templates', false);
    try {
        echo $twig->render('form.twig', [
            'form' => $form,
            'col' => $colAmount,
            'additionnalHtml' => $additionnalHtml,
            'root_doc' => $CFG_GLPI['root_doc'],
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function getHiddenInputsForItemForm($item, $options)
{
    return [
        $options['id'] != '' ? [
            'type' => 'hidden',
            'name' => 'entities_id',
            'value' => $item->fields['entities_id'],
        ] : [],
        $options['id'] != '' ? [
            'type' => 'hidden',
            'name' => 'is_recursive',
            'value' => $item->fields['is_recursive'],
        ] : [],
        [
            'type' => 'hidden',
            'name' => isset($options['id']) && $options['id'] != '' ? 'update' : 'add',
            'value' => '',
        ],
        [
            'type' => 'hidden',
            'name' => 'id',
            'value' => isset($options['id']) ? $options['id'] : 0,
        ],
        [
            'type' => 'hidden',
            'name' => '_glpi_csrf_token',
            'value' => Session::getNewCSRFToken(),
        ],
        [
            'type' => 'hidden',
            'name' => '_read_date_mod',
            'value' => (new DateTime)->format('Y-m-d H:i:s'),
        ],
    ];
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
