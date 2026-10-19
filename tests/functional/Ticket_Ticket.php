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

use DbTestCase;

/* Test for inc/ticket_ticket.class.php */

class Ticket_Ticket extends DbTestCase
{
    private $tone;
    private $ttwo;

    private function createTickets()
    {
        $tone = new \Ticket();
        $this->integer(
            (int)$tone->add([
              'name'         => 'Linked ticket 01',
              'description'  => 'Linked ticket 01',
              'content'      => 'Linked ticket 01',
         ])
        )->isGreaterThan(0);
        $this->boolean($tone->getFromDB($tone->getID()))->isTrue();
        $this->tone = $tone;

        $ttwo = new \Ticket();
        $this->integer(
            (int)$ttwo->add([
              'name'         => 'Linked ticket 02',
              'description'  => 'Linked ticket 02',
              'content'      => 'Linked ticket 02',
         ])
        )->isGreaterThan(0);
        $this->boolean($ttwo->getFromDB($ttwo->getID()))->isTrue();
        $this->ttwo = $ttwo;
    }

    public function testSimpleLink()
    {
        $this->createTickets();
        $tone = $this->tone;
        $ttwo = $this->ttwo;

        $link = new \Ticket_Ticket();
        $lid = (int)$link->add([
           'tickets_id_1' => $tone->getID(),
           'tickets_id_2' => $ttwo->getID(),
           'link'         => \Ticket_Ticket::LINK_TO
        ]);
        $this->integer($lid)->isGreaterThan(0);

        //cannot add same link twice!
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::LINK_TO
         ])
        )->isIdenticalTo(0);

        //but can be reclassed as a duplicate
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::DUPLICATE_WITH
         ])
        )->isGreaterThan(0);
        //original link has been removed
        $this->boolean($link->getFromDB($lid))->isFalse();

        //cannot eclass from duplicate to simple link
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::LINK_TO
         ])
        )->isIdenticalTo(0);
    }

    public function testSonsParents()
    {
        $this->createTickets();
        $tone = $this->tone;
        $ttwo = $this->ttwo;

        $link = new \Ticket_Ticket();
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::SON_OF
         ])
        )->isGreaterThan(0);

        //cannot add same link twice!
        $link = new \Ticket_Ticket();
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::SON_OF
         ])
        )->isIdenticalTo(0);

        $this->createTickets();
        $tone = $this->tone;
        $ttwo = $this->ttwo;

        $link = new \Ticket_Ticket();
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::PARENT_OF
         ])
        )->isGreaterThan(0);
        $this->boolean($link->getFromDB($link->getID()))->isTrue();

        //PARENT_OF is stored as inversed child
        $this->array($link->fields)
           ->integer['tickets_id_1']->isIdenticalTo($ttwo->getID())
           ->integer['tickets_id_2']->isIdenticalTo($tone->getID())
           ->integer['link']->isEqualTo(\Ticket_Ticket::SON_OF);
    }

    public function testRelationshipLabelsFromBothTickets()
    {
        require_once GLPI_ROOT . '/src/twig/twig.class.php';
        $twig = \Twig::load(GLPI_ROOT . '/templates', false);
        $relations = [
            \Ticket_Ticket::LINK_TO => __('Linked to'),
            \Ticket_Ticket::DUPLICATE_WITH => __('Duplicates'),
            \Ticket_Ticket::SON_OF => __('Son of'),
            \Ticket_Ticket::PARENT_OF => __('Parent of'),
        ];
        $cases = [
            [\Ticket_Ticket::SON_OF, __('Son of'), __('Parent of')],
            [\Ticket_Ticket::PARENT_OF, __('Parent of'), __('Son of')],
            [\Ticket_Ticket::DUPLICATE_WITH, __('Duplicates'), __('Duplicated by')],
            [\Ticket_Ticket::LINK_TO, __('Linked to'), __('Linked to')],
        ];

        foreach ($cases as [$type, $firstLabel, $secondLabel]) {
            $this->createTickets();
            $link = new \Ticket_Ticket();
            $linkId = (int)$link->add([
                'tickets_id_1' => $this->tone->getID(),
                'tickets_id_2' => $this->ttwo->getID(),
                'link' => $type,
            ]);
            $this->integer($linkId)->isGreaterThan(0);

            foreach ([$this->tone->getID() => $firstLabel, $this->ttwo->getID() => $secondLabel] as $ticketId => $expectedLabel) {
                $links = \Ticket_Ticket::getLinkedTicketsTo($ticketId);
                $this->string($links[$linkId]['link_name'])->isIdenticalTo($expectedLabel);
                $html = $twig->render('macros/inputs/ticketSelect.twig', [
                    'attributes' => [
                        'name' => '_link',
                        'ticket_id' => $ticketId,
                        'relations' => $relations,
                        'values' => $links,
                    ],
                ]);
                $document = new \DOMDocument();
                $markup = preg_replace('~<script\b[^>]*>.*?</script>~is', '', $html);
                $document->loadHTML($markup, LIBXML_NOERROR | LIBXML_NOWARNING);
                $xpath = new \DOMXPath($document);
                $label = $xpath->evaluate('string(//tr[@data-link-id="' . $linkId . '"]/td[1])');
                $this->string(trim($label))->isIdenticalTo($expectedLabel);
            }
        }
    }

    public function testNumberOpen()
    {
        $this->login();
        $this->createTickets();
        $tone = $this->tone;
        $ttwo = $this->ttwo;

        $link = new \Ticket_Ticket();
        $this->integer(
            (int)$link->add([
              'tickets_id_1' => $tone->getID(),
              'tickets_id_2' => $ttwo->getID(),
              'link'         => \Ticket_Ticket::LINK_TO
         ])
        )->isGreaterThan(0);

        //not a SON_OF => no child
        $this->integer($link->countOpenChildren($link->getID()))->isIdenticalTo(0);

        $this->boolean(
            $link->update([
              'id'     => $link->getID(),
              'link'   => \Ticket_Ticket::SON_OF
         ])
        )->isTrue();
        $this->integer($link->countOpenChildren($ttwo->getID()))->isIdenticalTo(1);

        $this->boolean(
            $tone->update([
              'id'     => $tone->getID(),
              'status' => \Ticket::CLOSED
         ])
        )->isTrue();
        $this->integer($link->countOpenChildren($ttwo->getID()))->isIdenticalTo(0);
    }
}
