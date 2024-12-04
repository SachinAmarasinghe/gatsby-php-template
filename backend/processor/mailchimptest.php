<?php
// Include your Mailchimp function file
include('mailchimp-register.php');

// Read the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// If test data is received
if ($data) {
    // Call the syncToMailchimp function with test data
    syncToMailchimp(
        $data['email'],
        $data['first_name'],
        $data['last_name'],
        $data['phone'],
        $data['are_you_a_realtor'],
        $data['postal_code'],
        $data['are_you_working_with_realtor'],
        $data['database'],
        $data['tag']
    );
    
    echo "Sync to Mailchimp triggered successfully!";
} else {
    echo "No data received.";
}
?>
