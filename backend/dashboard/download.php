<?php
require_once 'auth.php';
require_once '../database.php';

// Fetch all registrants
$query = "SELECT id, email, first_name, last_name, postal_code, how_did_you_hear, is_realtor, created_at FROM registrations";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=registrants.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, ['ID', 'Email', 'First Name', 'Last Name', 'Postal Code', 'How Did You Hear', 'Realtor', 'Registered At']);

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['email'],
        $row['first_name'],
        $row['last_name'],
        $row['postal_code'],
        $row['how_did_you_hear'],
        $row['is_realtor'] ? 'Yes' : 'No',
        $row['created_at']
    ]);
}

// Close output stream
fclose($output);
exit;
