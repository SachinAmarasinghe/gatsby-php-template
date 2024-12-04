<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('mailchimp-register.php');

//Local variables
$table =  "sean_1_rainwater_registration";
$site = "SEAN 1 Rainwater"; // Site o-ame
$emailSentFrom = 'barrie@sean.ca';
$thankYouURL = "/thank-you.html";
$recaptcha_secret = '6LewFR4qAAAAAKExBY3AVwiZ7KNpjyzN-36kpolJ';
//sitekey=6LewFR4qAAAAAJsfm_kIQyQmdUiLl7X-DmA9qqSN

//NOTIFICATIONS Settings
$template = 'templates/notification.html';
//To add  Receipents
$notifyAlsoTo = array(
    'barrie@sean.ca' => 'Barrie sales',
);


// $notifyAlsoTo = array(
//     'apatel@ryan-design.com' => 'Ryan Design',
//     'krista@milborne.com' => 'Ryan Design',
//     'dallegranzarealty@gmail.com' => 'Ryan Design',
//     'samarasinghe@ryan-design.com' => 'Ryan Design'
// );


//'richmondhillgraceSM@gmail.com' => 'Ryan Design',

//AUTORESPONDER SETTINGS, If no auto responder leave Blank ''
$autoresponder = '';
$autoresponderSubject = "Thank You For Registering";


/*


  `first_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT '',
  `email` varchar(200) DEFAULT '',
  `city` varchar(200) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `postal_code` varchar(20) DEFAULT '',
  `are_you_working_with_broker` varchar(20) NOT NULL DEFAULT '',
  `how_did_you_hear` varchar(50) NOT NULL DEFAULT '',
  `are_you_a_realtor` varchar(20) NOT NULL DEFAULT '',
  `interested_in_homes` varchar(10) NOT NULL DEFAULT '',
  `broker_name` varchar(200) NOT NULL DEFAULT '',
  `created_at` date NOT NULL,

*/

//Get data into array
$allArray = array(
    array(
        "data" => $_POST["first_name"],
        "field" => "first_name",
        "title" => "First Name"
    ),
    array(
        "data" => $_POST["last_name"],
        "field" => "last_name",
        "title" => "Last Name"
    ),
    array(
        "data" => $_POST["email"],
        "field" => "email",
        "title" => "Email"
    ),
    array(
        "data" => $_POST["phone"],
        "field" => "phone",
        "title" => "Phone"
    ),
    array(
        "data" => $_POST["postal_code"],
        "field" => "postal_code",
        "title" => "Postcode"
    ),
    // array(
    //     "data" =>  implode(', ', $_POST["interested_in_homes"]),
    //     "field" => "interested_in_homes",
    //     "title" => "Interested In Homes"), 
    // array(
    //     "data" =>  implode(', ', $_POST["community"]),
    //     "field" => "community",
    //     "title" => "Community"), 
    array(
        "data" => $_POST["are_you_a_realtor"],
        "field" => "are_you_a_realtor",
        "title" => "Are you a Realtor?"
    ),
    array(
        "data" => $_POST["are_you_working_with_realtor"],
        "field" => "are_you_working_with_realtor",
        "title" => "ARE YOU WORKING WITH A REALTOR?"
    ),
    array(
        "data" => $_POST["realtor_name"],
        "field" => "realtor_name",
        "title" => "Realtor Name"
    ),
    //UTM tag fields
    array(
        "data" => $_POST["utm_source"],
        "field" => "utm_source",
        "title" => "UTM Source"
    ),
    array(
        "data" => $_POST["utm_medium"],
        "field" => "utm_medium",
        "title" => "UTM Medium"
    ),
    array(
        "data" => $_POST["utm_campaign"],
        "field" => "utm_campaign",
        "title" => "UTM Campaign"
    ),


);
$database = '1_Rainwater';
$tag = '1 Rainwater';
include 'processor.php';
exit();
