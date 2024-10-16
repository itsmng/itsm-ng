<?php

include('../inc/includes.php');

Session::checkLoginUser();

$totalTickets = countElementsInTable('glpi_tickets', ['entities_id' => $_SESSION['glpiactive_entity']]);
$newTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::INCOMING, 'entities_id' => $_SESSION['glpiactive_entity']]);
$pendingTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::WAITING, 'entities_id' => $_SESSION['glpiactive_entity']]);
$assignedTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::ASSIGNED, 'entities_id' => $_SESSION['glpiactive_entity']]);
$plannedTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::PLANNED, 'entities_id' => $_SESSION['glpiactive_entity']]);
$solvedTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::SOLVED, 'entities_id' => $_SESSION['glpiactive_entity']]);
$closedTickets = countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::CLOSED, 'entities_id' => $_SESSION['glpiactive_entity']]);

$ticketData = [
    $newTickets,
    $pendingTickets,
    $assignedTickets,
    $plannedTickets,
    $solvedTickets,
    $closedTickets,
];

// Json Parsing
header('Content-Type: application/json');
echo json_encode($ticketData);
