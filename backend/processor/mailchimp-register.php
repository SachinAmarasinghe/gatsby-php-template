<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

function syncToMailchimp($email, $first_name, $last_name, $phone, $realtor, $postcode, $wrealtor, $database, $tag)
{
    // Mailchimp API key and Audience List ID

    $listId = '9fd01dfcc3';

    // Mailchimp API endpoint
    $serverPrefix = 'us19'; // Replace with your server prefix (e.g., us5, us19, etc.)
    $url = "https://$serverPrefix.api.mailchimp.com/3.0/lists/$listId/members/";

    // Prepare the data
    $data = [
        'email_address' => $email,
        'status' => 'subscribed',
        'merge_fields'  => [
            'FNAME' => $first_name,
            'LNAME' => $last_name,
            'PHONE' => $phone,
            'REALTOR' => $realtor,
            'POSTCODE' => $postcode,
            'WREALTOR' => $wrealtor,
            'DATABASE' => $database
        ],
    ];

    // Encode the data to JSON format
    $jsonData = json_encode($data);

    // Initialize cURL
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_USERPWD, 'anystring:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Execute cURL and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the response to get the subscriber hash
    $response_data = json_decode($response, true);

    // If subscriber was added successfully, add tags
    if (isset($response_data['id'])) {
        // Subscriber hash (MD5 hash of lowercase email)
        $subscriber_hash = md5(strtolower($email));

        // Tags you want to add
        $tags = [
            'tags' => [
                ['name' => $tag, 'status' => 'active'],
            ]
        ];

        // Mailchimp tags endpoint
        $tags_url = "https://$serverPrefix.api.mailchimp.com/3.0/lists/$listId/members/$subscriber_hash/tags";

        // Convert tags array to JSON
        $json_tags = json_encode($tags);

        // Initialize cURL session for adding tags
        $ch_tags = curl_init($tags_url);
        curl_setopt($ch_tags, CURLOPT_USERPWD, 'user:' . $apiKey);  // Add your Mailchimp API key
        curl_setopt($ch_tags, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch_tags, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_tags, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_tags, CURLOPT_POST, true);
        curl_setopt($ch_tags, CURLOPT_POSTFIELDS, $json_tags);

        // Execute cURL and get the response for tags
        $tags_response = curl_exec($ch_tags);
        curl_close($ch_tags);

        // Handle tags response (optional)
        $tags_response_data = json_decode($tags_response, true);
        if (isset($tags_response_data['tags'])) {
            echo "Tags added successfully.";
        } else {
            echo "Failed to add tags: " . $tags_response;
        }
    } else {
        echo "Failed to add subscriber: " . $response;
    }
}
