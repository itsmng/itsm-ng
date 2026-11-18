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

namespace tests\units;

class RequestType extends \DbTestCase
{
    public function testTicketHighlightSettings()
    {
        $this->login();
        $source = new \RequestType();
        $id = (int) $source->add(['name' => 'Highlight test']);
        $this->integer($id)->isGreaterThan(0);
        $source->getFromDB($id);
        $this->integer((int) $source->fields['highlight_ticket_rows'])->isIdenticalTo(0);
        $this->integer((int) $source->fields['highlight_ticket_page'])->isIdenticalTo(0);
        $this->array(\RequestType::getTicketHighlightColors('rows'))->notHasKey($id);
        $this->array(\RequestType::getTicketHighlightColors('page'))->notHasKey($id);

        foreach ([[1, 0], [0, 1], [1, 1], [0, 0]] as [$rows, $page]) {
            $this->boolean($source->update([
                'id' => $id,
                'ticket_highlight_color' => '#ABCDEF',
                'highlight_ticket_rows' => $rows,
                'highlight_ticket_page' => $page,
            ]))->isTrue();
            $source->getFromDB($id);
            $this->string($source->fields['ticket_highlight_color'])->isIdenticalTo('#abcdef');
            $this->boolean(isset(\RequestType::getTicketHighlightColors('rows')[$id]))->isIdenticalTo((bool) $rows);
            $this->boolean(isset(\RequestType::getTicketHighlightColors('page')[$id]))->isIdenticalTo((bool) $page);
        }
        // An unrelated update must preserve settings omitted from the input.
        $source->update(['id' => $id, 'highlight_ticket_rows' => 1]);
        $source->update(['id' => $id, 'name' => 'Renamed source', 'is_active' => 0]);
        $this->string(\RequestType::getTicketHighlightColors('rows')[$id])->isIdenticalTo('#abcdef');
        $this->array(\RequestType::getTicketHighlightColors('invalid'))->isEmpty();

        $this->string(\RequestType::getTicketHighlightTextColor('#fff2cc'))->isIdenticalTo('#000000');
        $this->string(\RequestType::getTicketHighlightTextColor('#234567'))->isIdenticalTo('#ffffff');
    }

    public function testRejectInvalidTicketHighlightColors()
    {
        $this->login();
        $source = new \RequestType();
        $id = (int) $source->add(['name' => 'Valid source', 'ticket_highlight_color' => '#123456']);
        foreach ([null, 'red', '#abc', '#123456;', '#123456' . "\n", ['#123456']] as $color) {
            $this->boolean($source->update(['id' => $id, 'ticket_highlight_color' => $color]))->isFalse();
            unset($_SESSION['MESSAGE_AFTER_REDIRECT'][ERROR]);
            $source->getFromDB($id);
            $this->string($source->fields['ticket_highlight_color'])->isIdenticalTo('#123456');
        }
        global $DB;
        $DB->update('glpi_requesttypes', ['ticket_highlight_color' => 'invalid', 'highlight_ticket_rows' => 1], ['id' => $id]);
        $this->array(\RequestType::getTicketHighlightColors('rows'))->notHasKey($id);
    }

    public function testRequestSourceAvailableWithoutVisibleColumn()
    {
        $this->login();
        $source = new \RequestType();
        $id = (int) $source->add(['name' => 'Search highlight', 'highlight_ticket_rows' => 1]);
        $ticket = new \Ticket();
        $ticketId = (int) $ticket->add([
            'name' => 'Highlighted ticket',
            'content' => 'Highlight regression test',
            'requesttypes_id' => $id,
            'entities_id' => $_SESSION['glpiactive_entity'],
        ]);
        $this->integer($ticketId)->isGreaterThan(0);
        $data = \Search::getDatas('Ticket', [
            'criteria' => [['field' => 2, 'searchtype' => 'equals', 'value' => $ticketId]],
            'forcedisplay' => [1],
        ]);
        $this->integer((int) $data['data']['rows'][0]['requesttypes_id'])->isIdenticalTo($id);
        $values = \Ticket::showShort($ticketId, [
            'return_values' => true,
            'ticket_highlight_colors' => \RequestType::getTicketHighlightColors('rows'),
        ]);
        $this->string($values['_ticketHighlight'])->isIdenticalTo('#fff2cc');
    }
}
