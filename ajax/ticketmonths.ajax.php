<?php

include('../inc/includes.php');
global $DB;

// Get the last 6 months
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-$i months"));
}

// DQL request for 6 last months tickets
$queryBuilder = $entityManager->createQueryBuilder();
$queryBuilder
    ->select('YEAR(t.date) AS year, MONTH(t.date) AS month, COUNT(t.id) AS ticket_count')
    ->from(\Itsmng\Domain\Entities\Ticket::class, 't')
    ->where('t.date >= :sixMonthsAgo')
    ->setParameter('sixMonthsAgo', $sixMonthsAgo)
    ->groupBy('year, month')
    ->orderBy('year', 'ASC')
    ->addOrderBy('month', 'ASC');

$results = $queryBuilder->getQuery()->getArrayResult();

// Initialize ticketData array with all months set to 0
$ticketData = array_fill(0, 6, 0);
$monthIndex = array_flip($months);

// Fetch results and populate ticketData array with actual ticket counts
foreach ($results as $row) {
    $month = $row['month'];
    if (!isset($monthIndex[$month])) {
        continue;
    }
    $ticketData[$monthIndex[$month]] = (int)$row['ticket_count'];
}

// json parsing
header('Content-Type: application/json');
echo json_encode(array_values($ticketData));
