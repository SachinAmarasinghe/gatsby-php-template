<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if both 'id' and 'table' are provided
    if (!isset($_POST['id']) || !isset($_POST['table'])) {
        $_SESSION['error_message'] = "Record or table not specified!";
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    }

    $recordId = mysqli_real_escape_string($connection, $_POST['id']);
    $recordTable = mysqli_real_escape_string($connection, $_POST['table']);

    // Build the update query
    $updateFields = [];
    foreach ($_POST as $field => $value) {
        if ($field !== 'id' && $field !== 'table') {
            // Escape values and skip disabled (non-editable) fields
            $escapedValue = mysqli_real_escape_string($connection, $value);
            $updateFields[] = "$field = '$escapedValue'";
        }
    }

    if (!empty($updateFields)) {
        $sql = "UPDATE $recordTable SET " . implode(', ', $updateFields) . " WHERE id = $recordId";

        if (mysqli_query($connection, $sql)) {
            $_SESSION['success_message'] = "Record updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating record: " . mysqli_error($connection);
        }
    } else {
        $_SESSION['error_message'] = "No fields to update!";
    }
    
    // Redirect back to the update page or another page with messages
    header("Location: /processor/admin/updateRecord.php?id=$recordId&table=$recordTable");
    exit;
}
?>
