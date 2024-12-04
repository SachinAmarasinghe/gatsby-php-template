<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

include '../config.php';


if (isset($_GET['table']))
    $table_name = $_GET['table'];
else
    $table_name = 'sean_registration';


if (isset($_GET['orderby']))
    $order_by = $_GET['orderby'];
else
    $order_by  = 'id';

if (isset($_GET['sortby']))
    $sort = $_GET['sortby'];
else
    $sort  = 'asc';

if (isset($_GET['days']))
    $days_selected = $_GET['days'];
else
    $days_selected = 7;



$sql = "SELECT * FROM " . $table_name . " order by  " . $order_by .  " " . $sort;
$result = mysqli_query($connection, $sql);

if ($result)
    $rowCount = mysqli_num_rows($result);

// Attempt select query execution


if (isset($_GET['download']) && $_GET['download'] == 'true') {
    $fp = fopen('php://output', 'w');
    if ($fp) {

        //$sqlRegistrants = "SELECT * FROM  bas_registrants $whereClause ORDER BY id $limit";
        // $sql = "SELECT ". $_GET['fields'] ." FROM ".  $_GET['table'] ." ORDER BY id desc";
        $result = mysqli_query($connection, $sql);


        $headerNames = array();
        $headerNames = $adminTables["$table_name"]['fields'];
        array_unshift($headerNames, "S. NO.");
        $headerNames  = array_map('strtoupper', $headerNames);

        $fileName = "registrantions_" . strtolower(str_replace(' ', '_', $adminTables["$table_name"]['title']))   . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        fputcsv($fp, $headerNames);


        $count = 0;
        while ($row = mysqli_fetch_array($result)) {

            $currentRowArray = array();

            $currentRowArray[] = ++$count;
            foreach ($adminTables["$table_name"]['fields'] as $td) {
                $currentRowArray[] = $row[$td];
            }


            $rowArray = $currentRowArray;


            fputcsv($fp, $rowArray);
        }
    }
    mysqli_free_result($result);
    exit();
}

// Get the current and previous month names
$currentMonth = new DateTime();
$lastMonth = new DateTime();
$lastToLastMonth = new DateTime();

$lastMonth->modify('-1 month');
$lastToLastMonth->modify('-2 months');

$currentMonthName = $currentMonth->format('F'); // Example: June
$lastMonthName = $lastMonth->format('F');      // Example: May
$lastToLastMonthName = $lastToLastMonth->format('F'); // Example: April

// Total records
$sqlTotal = "SELECT COUNT(*) AS total FROM $table_name";
$resultTotal = mysqli_query($connection, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalRecords = $rowTotal['total'];

// Realtors
// Check if 'are_you_a_realtor' column exists
$columnCheckQuery = "SHOW COLUMNS FROM $table_name LIKE 'are_you_a_realtor'";
$columnCheckResult = mysqli_query($connection, $columnCheckQuery);

if (mysqli_num_rows($columnCheckResult) > 0) {
    // 'are_you_a_realtor' column exists
    $realtorsTotal = "SELECT COUNT(*) AS total FROM $table_name WHERE are_you_a_realtor='Yes'";
} else {
    // Fallback to 'broker' column
    $realtorsTotal = "SELECT COUNT(*) AS total FROM $table_name WHERE broker='Yes'";
}

$resultRealtors = mysqli_query($connection, $realtorsTotal);
$rowTotalRealtors = mysqli_fetch_assoc($resultRealtors);
$realtors = $rowTotalRealtors['total'];

// registrants
$totalRegistrants = $totalRecords - $realtors;

// Records in the last 7 days
$sqlLast7Days = "SELECT COUNT(*) AS last7Days FROM $table_name WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLast7Days = mysqli_query($connection, $sqlLast7Days);
$rowLast7Days = mysqli_fetch_assoc($resultLast7Days);
$last7DaysRecords = $rowLast7Days['last7Days'];

// Records in the last to last 7 days
$sqlLastToLast7Days = "SELECT COUNT(*) AS lastToLast7Days FROM $table_name WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLastToLast7Days = mysqli_query($connection, $sqlLastToLast7Days);
$rowLastToLast7Days = mysqli_fetch_assoc($resultLastToLast7Days);
$lastToLast7DaysRecords = $rowLastToLast7Days['lastToLast7Days'];

// Records in the current month
$sqlCurrentMonth = "SELECT COUNT(*) AS currentMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
$resultCurrentMonth = mysqli_query($connection, $sqlCurrentMonth);
$rowCurrentMonth = mysqli_fetch_assoc($resultCurrentMonth);
$currentMonthRecords = $rowCurrentMonth['currentMonth'];

// Records in the last month
$sqlLastMonth = "SELECT COUNT(*) AS lastMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
$resultLastMonth = mysqli_query($connection, $sqlLastMonth);
$rowLastMonth = mysqli_fetch_assoc($resultLastMonth);
$lastMonthRecords = $rowLastMonth['lastMonth'];

// Records in the last to last month
$sqlLastToLastMonth = "SELECT COUNT(*) AS lastToLastMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')";
$resultLastToLastMonth = mysqli_query($connection, $sqlLastToLastMonth);
$rowLastToLastMonth = mysqli_fetch_assoc($resultLastToLastMonth);
$lastToLastMonthRecords = $rowLastToLastMonth['lastToLastMonth'];
?>

<?php include("./includes/meta.php") ?>
<title>Admin Panel, Download Form Data</title>

</head>

<body class="admin-page">
    <div class="container-fluid">
        <div class="admin-header">
            <div class="logo">
                <h1>Registrants</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <select name="" id="tblSelect" class="form-control mb-5 mt-5">
                    <option value="">TABLES AVAILABLE</option>
                    <?php
                    foreach ($adminTables as $key => $table) { ?>
                        <option value="<?= $table['table_name']  ?>"><?= $table['title'] ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
        if ($table_name &&  $rowCount) {
        ?>
            <div class="row mb-4">
                <div class="col-12">
                    <h1><?= $adminTables["$table_name"]['title'] ?></h1>
                </div>
                <div class="col-12 pt-3">
                    <div class="info_pill_container">
                        <div class="info_pill">
                            <span>Total Records</span>
                            <h2><?= number_format($totalRecords) ?></h2>
                        </div>
                        <div class="info_pill">
                            <span>Realtors</span>
                            <h2><?= number_format($realtors) ?></h2>
                        </div>
                        <div class="info_pill">
                            <span>Registrants</span>
                            <h2><?= number_format($totalRegistrants) ?></h2>
                        </div>
                        <div class="info_pill">
                            <span>Last 7 Days</span>
                            <h2><?= $last7DaysRecords ?></h2>
                        </div>
                        <div class="info_pill">
                            <span>Last to Last 7 Days</span>
                            <h2><?= $lastToLast7DaysRecords ?></h2>
                        </div>
                        <div class="info_pill">
                            <span><?= $currentMonthName ?></span>
                            <h2><?= $currentMonthRecords ?></h2>
                        </div>
                        <div class="info_pill">
                            <span><?= $lastMonthName ?></span>
                            <h2><?= $lastMonthRecords ?></h2>
                        </div>
                        <div class="info_pill">
                            <span><?= $lastToLastMonthName ?></span>
                            <h2><?= $lastToLastMonthRecords ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-right pt-3">
                    <a href="/processor/admin/upload-records.php" class="btn btn-primary big">Upload records</a>
                    <a href="<?= $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'] ?>&download=true" class="btn btn-success big"><i class="fa-solid fa-file-csv"></i> Export to CSV File</a> |
                    <a href="logout.php" class="btn btn-danger big"><i class="fa-solid fa-right-from-bracket"></i> LOG OUT</a>
                </div>
            </div>

        <?php
        }
        ?>
    </div>
    <?php
    if ($table_name && $rowCount) {
    ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12" id="tblData">
                    <table class="table table-striped table-hover table-sm table-responsive custom-table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-nowrap" scope="col">S. No.</th>
                                <?php
                                if (!empty($table_name) && $result) {
                                    if (mysqli_num_rows($result) > 0) {
                                        foreach ($adminTables["$table_name"]['fields'] as $thead) {
                                ?>
                                            <th scope="col">
                                                <a href="<?= $_SERVER["PHP_SELF"] ?>?table=<?= $table_name ?>&orderby=<?= $thead  ?>&sortby=<?= ((isset($_GET['sortby']) && $_GET['sortby'] == 'asc') ? 'desc' : 'asc') ?>" class=""><?= strtoupper(str_replace('_', ' ', $thead)) ?> </a>
                                            </th>
                                        <?php
                                        }
                                        ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                        $count = 1;
                                        while ($row = mysqli_fetch_array($result)) {
                            ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <?php

                                            foreach ($adminTables["$table_name"]['fields'] as $td) {
                                    ?>
                                        <td class=''><?= $row["$td"] ?></td>
                                    <?php
                                            }
                                    ?>
                                </tr>
                            <?php
                                        }
                            ?>
                        </tbody>
                    </table>
            <?php        // Free result set

                                    }
                                }
            ?>

                </div>
            </div>
        </div>
    <?php
    } else if ($table_name) {
    ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="text-danger"> No records matching your query were found. Pleased Check your Query!!! <br /><br />ERROR DESCRIPTION : <small>Table does not Exist OR No Records in Table</small></h5>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="text-danger"> SELECT TABLE FROM ABOVE</h5>
                </div>
            </div>
        </div>
    <?php
    }
    // mysqli_free_result($result);
    mysqli_close($connection);
    ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script>
        $(function() {
            // bind change event to select
            $('#tblSelect').on('change', function() {
                var url = $(this).val(); // get selected value
                window.location = '<?= $_SERVER["PHP_SELF"] ?>?table=' + url; // redirect
                return false;
            });
        });
    </script>
</body>

</html>