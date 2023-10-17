<?php

/**
 * @param $item
 * 
 * @global $DB
 * @return array
 */
function getFormForItemType($item) {
    global $DB; 
    $database = $DB->dbdefault;
    $table = $item->getTable();

    $columns = iterator_to_array($DB->request([
        'SELECT' => ['COLUMN_NAME', 'DATA_TYPE'],
        'FROM' => 'INFORMATION_SCHEMA.COLUMNS',
        'WHERE' => [
            'TABLE_SCHEMA' => $database,
            'TABLE_NAME' => $table,
        ]
    ]));
}

/**
 * @param $form
 * 
 */
function getOptionForItems($item, $conditions = [], $display_emptychoice = true)
{
    global $DB;

    $table = getTableForItemType($item);
    $iterator = $DB->request([
        'SELECT'          => ['id', 'name'],
        'FROM'            => $table,
        'WHERE'           => $conditions,
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

/**
 * @param $form
 * @param $additionnalHtml
 * 
 * @return string
 */
function renderTwigForm($form, $additionnalHtml, $colAmount = 2)
{
    require_once GLPI_ROOT . "/ng/twig.class.php";
    $twig = Twig::load(GLPI_ROOT . "/templates", false);
    try {
        echo $twig->render('form.twig', [
            'form' => $form,
            'col' => $colAmount,
            'additionnalHtml' => $additionnalHtml,
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function getHiddenInputsForItemForm($item, $options)
{
    return [
        [
            'type' => 'hidden',
            'name' => 'entities_id',
            'value' => $item->fields['entities_id'],
        ],
        [
            'type' => 'hidden',
            'name' => 'is_recursive',
            'value' => $item->fields['is_recursive'],
        ],
        [
            'type' => 'hidden',
            'name' => $options['id'] ? 'update' : 'add',
            'value' => '',
        ],
        [
            'type' => 'hidden',
            'name' =>  'id',
            'value' => $options['id'] ? $options['id'] : 0,
        ],
        [
            'type' => 'hidden',
            'name' => '_glpi_csrf_token',
            'value' => Session::getNewCSRFToken()
        ],
        [
            'type' => 'hidden',
            'name' => '_read_date_mod',
            'value' => (new DateTime())->format('Y-m-d H:i:s'),
        ]
    ];
}
