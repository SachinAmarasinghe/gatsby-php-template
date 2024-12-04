<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

require_once('../config.php');


?>
<?php include("./includes/meta.php") ?>
<title>Admin Panel, Upload Leads to Database</title>

</head>

<body class="admin-page">
    <?php include("includes/navigation.php") ?>
    <?php

    try {
        // Connect to the database using PDO
        $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all tables from the database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Check if a form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_table'])) {
            $selectedTable = $_POST['selected_table'];

            // Handle file upload
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
                // Create a unique file name to store the uploaded CSV
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
                }
                $csvFilePath = $uploadDir . uniqid() . '.csv';

                // Move the uploaded file to the permanent location
                if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFilePath)) {
                    $_SESSION['csv_file_path'] = $csvFilePath;

                    // Open the CSV file
                    $csvFile = fopen($csvFilePath, 'r');
                    $csvHeaders = fgetcsv($csvFile);

                    // Get the table columns
                    $stmt = $pdo->query("DESCRIBE $selectedTable");
                    $tableColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    echo "<div class='container mt-5'>";
                    echo "<h2 class='mb-4'>Map CSV Columns to Database Columns</h2>";
                    echo "<form method='POST'>";
                    echo "<input type='hidden' name='selected_table' value='$selectedTable'>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead><tr><th>CSV Columns</th><th>Database Columns</th></tr></thead>";
                    echo "<tbody>";

                    // Display dropdowns to map CSV columns to table columns
                    foreach ($csvHeaders as $csvHeader) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($csvHeader) . "</td>";
                        echo "<td>";
                        echo "<select name='column_mapping[" . htmlspecialchars($csvHeader) . "]' class='form-select'>";
                        echo "<option value=''>Skip</option>";
                        foreach ($tableColumns as $tableColumn) {
                            echo "<option value='" . htmlspecialchars($tableColumn) . "'>" . htmlspecialchars($tableColumn) . "</option>";
                        }
                        echo "</select>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    echo "</tbody>";
                    echo "</table>";
                    echo "<button type='submit' name='update_table' class='btn btn-primary'>Update Table</button>";
                    echo "</form>";
                    echo "</div>";

                    fclose($csvFile);
                } else {
                    echo "<div class='container mt-5'>";
                    echo "<div class='alert alert-danger'>Error: Failed to save uploaded file.</div>";
                    echo "</div>";
                }
            } elseif (isset($_POST['update_table'])) {
                $selectedTable = $_POST['selected_table'];
                $columnMapping = $_POST['column_mapping'];

                // Check if the CSV file path exists in the session
                if (isset($_SESSION['csv_file_path']) && file_exists($_SESSION['csv_file_path'])) {
                    $csvFile = fopen($_SESSION['csv_file_path'], 'r');

                    // Skip the header row
                    fgetcsv($csvFile);

                    // Begin transaction
                    $pdo->beginTransaction();

                    while ($row = fgetcsv($csvFile)) {
                        $updateData = [];
                        $insertColumns = [];
                        $insertPlaceholders = [];
                        $updateFields = [];

                        // Map CSV columns to table columns based on the user's selection
                        foreach ($columnMapping as $csvColumn => $dbColumn) {
                            if (!empty($dbColumn)) {
                                $updateData[$dbColumn] = $row[array_search($csvColumn, array_keys($columnMapping))];
                                $insertColumns[] = $dbColumn;
                                $insertPlaceholders[] = "?";
                                $updateFields[] = "$dbColumn = VALUES($dbColumn)"; // This will update only the new values
                            }
                        }

                        try {
                            // Prepare the insert statement with ON DUPLICATE KEY UPDATE to handle both insert and update
                            $stmt = $pdo->prepare(
                                "INSERT INTO $selectedTable (" . implode(', ', $insertColumns) . ") 
            VALUES (" . implode(', ', $insertPlaceholders) . ") 
            ON DUPLICATE KEY UPDATE " . implode(', ', $updateFields)
                            );

                            // Execute the insert or update
                            $stmt->execute(array_values($updateData));
                        } catch (PDOException $e) {
                            // Log or display the error and continue to the next record
                            echo "Error processing record: " . $e->getMessage() . "<br>";
                            continue; // Skip to the next iteration
                        }
                    }

                    // Commit transaction
                    $pdo->commit();

                    echo "<div class='container mt-5'>";
                    echo "<div class='alert alert-success'>Table updated successfully!</div>";
                    echo "<a href='/processor/admin/home.php'>Go back</a>";
                    echo "</div>";

                    fclose($csvFile);
                    unlink($_SESSION['csv_file_path']); // Delete the file after processing
                    unset($_SESSION['csv_file_path']); // Clear the file path from the session
                } else {
                    echo "<div class='container mt-5'>";
                    echo "<div class='alert alert-danger'>Error: The CSV file could not be found. Please upload the file again.</div>";
                    echo "</div>";
                }
            }
        } else {
            // Display the form to select a table and upload a CSV file
            echo '<div class="container mt-5">';
            echo '<h2 class="mb-4">Update Database from CSV</h2>';
            echo '<form method="POST" enctype="multipart/form-data">';
            echo '<div class="mb-3">';
            echo '<label for="table" class="form-label">Select a table to update:</label>';
            echo '<select name="selected_table" id="table" class="form-select">';

            foreach ($tables as $table) {
                echo '<option value="' . htmlspecialchars($table) . '">' . htmlspecialchars($table) . '</option>';
            }

            echo '</select>';
            echo '</div>';
            echo '<div class="mb-3">';
            echo '<label for="csv_file" class="form-label">Upload CSV file:</label>';
            echo '<input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv">';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary">Next</button>';
            echo '</form>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo "<div class='container mt-5'>";
        echo "<div class='alert alert-danger'>Connection failed: " . $e->getMessage() . "</div>";
        echo "<div><a href='/processor/admin/home.php' class='btn btn-primary'>Go Back</a></div>";
        echo "</div>";
    }

    ?>

</body>