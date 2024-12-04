<?php
define("DB_SERVER", "localhost");
define("DB_NAME", "sean_2024");
define("DB_USER", "web2024");
define("DB_PASS", "2vmihXCalM]yf&f#20O%wRbX");
date_default_timezone_set('US/Eastern');



// List of Tables &  fields for Admin Downloads
$adminTables = array(
    'sean_registration' => array(
        'table_name' => "sean_registration",
        'title' => 'Sean VIP Registration',
        'fields' => array('first_name', 'last_name', 'email', 'phone', 'postal_code', 'interested_in_homes', 'community', 'are_you_a_realtor', 'are_you_working_with_realtor', 'realtor_name', 'created_at', 'notes', 'utm_source', 'utm_medium', 'utm_campaign')
    ),
    'sean_283_rainwater_registration' => array(
        'table_name' => "sean_283_rainwater_registration",
        'title' => '238 Rainwater',
        'fields' => array('first_name', 'last_name', 'email', 'phone', 'postal_code', 'are_you_a_realtor', 'are_you_working_with_realtor', 'realtor_name', 'notes', 'created_at', 'utm_source', 'utm_medium', 'utm_campaign')
    ),
    'sean_1_rainwater_registration' => array(
        'table_name' => "sean_1_rainwater_registration",
        'title' => '1 Rainwater',
        'fields' => array('first_name', 'last_name', 'email', 'phone', 'postal_code', 'are_you_a_realtor', 'are_you_working_with_realtor', 'realtor_name', 'created_at', 'notes', 'utm_source', 'utm_medium', 'utm_campaign')
    ),
    'sean_bracebridge_registration' => array(
        'table_name' => "sean_bracebridge_registration",
        'title' => 'Bracebridge',
        'fields' => array('first_name', 'last_name', 'email', 'phone', 'postal_code', 'are_you_a_realtor', 'are_you_working_with_realtor', 'realtor_name', 'created_at', 'notes',  'utm_source', 'utm_medium', 'utm_campaign')
    )
);




//NO CHANGES REQUIRED BELOW THIS
$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if ($connection->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$mail = new PHPMailer(true);
$mail->isSMTP();  // Send using SMTP

//  $mail->SMTPDebug = 1; 

$mail->SMTPAuth   = true; // Enable SMTP authentication 
$mail->SMTPSecure = "tls";  // TLS
$mail->Host       = "mail.armadadata.com";
$mail->Port       = 25;

$mail->Username   = "sales@rosewoodurbantowns.com";
$mail->Password   = "tvnu_oUxrgv7";

function getUserIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
