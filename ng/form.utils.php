<?php

/**
 * @param $form
 * 
 */
function expandForm(&$form)
{
    foreach ($form['content'] as $bloc_key => $bloc) {
        foreach ($bloc['inputs'] as $input_key => $input) {
            if ($input['type'] == 'dropdown' && isset($input['from']['item'])) {
                $table = getTableForItemType($input['from']['item']);
                global $DB;
                $iterator = $DB->request([
                    'SELECT'          => ['id', 'name'],
                    'FROM'            => $table,
                    'WHERE'           => isset($input['from']['conditions']) ? $input['from']['conditions'] : []
                ]);
                while ($item = $iterator->next()) {
                    $input['from']['array'][$item['id']] = $item['name'];
                }
                $form['content'][$bloc_key]['inputs'][$input_key] = $input;
            }
        }
    }
}

/**
 * @param $form
 * @param $additionnalHtml
 * 
 * @return string
 */
function renderTwigForm($form, $additionnalHtml)
{
    require_once GLPI_ROOT . "/ng/twig.class.php";
    $twig = Twig::load(GLPI_ROOT . "/templates", false);
    try {
        echo $twig->render('form.twig', [
            'form' => $form,
            'col' => 2,
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
