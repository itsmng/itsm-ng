<?php

include('../inc/includes.php');
global $DB;

// Get the last 6 months
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-$i months"));
}

// SQL request for 6 last months tickets
$sql = "
SELECT DATE_FORMAT(date, '%Y-%m') AS month, COUNT(*) AS ticket_count
FROM glpi_tickets
WHERE date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY month
ORDER BY month;
";

// Results treatment
$result = $DB->request($sql);

// Initialize ticketData array with all months set to 0
$ticketData = array_fill(0, 6, 0);
$monthIndex = array_flip($months);

// Fetch results and populate ticketData array with actual ticket counts
while ($row = $result->next()) {
    $month = $row['month'];
    if (!isset($monthIndex[$month])) {
        continue;
    }
    $ticketData[$monthIndex[$month]] = (int)$row['ticket_count'];
}

// json parsing
header('Content-Type: application/json');
echo json_encode(array_values($ticketData));
