<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_POST['username'], $_POST['password'])) {
    exit('Please fill both the username and password fields!');
}

$credentials = [
    'admin' => '567892',
    'sean@homes' => 'usGEj522z7',
];

$username = test_input($_POST['username']);
$password = test_input($_POST['password']);

$is_valid = false;

foreach ($credentials as $user => $pass) {
    if ($username === $user && $password === $pass) {
        $_SESSION['loggedin'] = TRUE;
        $is_valid = true;
        break;
    }
}

if ($is_valid) {
    header('Location: home.php');
    exit; // Ensure no further code is executed
} else {
    echo 'Incorrect username and/or password!';
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
