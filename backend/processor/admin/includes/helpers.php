<?php
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

// what is the realtor column 
$realtorColumn = "";

if (mysqli_num_rows($columnCheckResult) > 0) {
    // 'are_you_a_realtor' column exists
    $realtorColumn = "are_you_a_realtor";
    $realtorsTotal = "SELECT COUNT(*) AS total FROM $table_name WHERE are_you_a_realtor='Yes'";
} else {
    // Fallback to 'broker' column
    $realtorColumn = "broker";
    $realtorsTotal = "SELECT COUNT(*) AS total FROM $table_name WHERE broker='Yes'";
}

$resultRealtors = mysqli_query($connection, $realtorsTotal);
$rowTotalRealtors = mysqli_fetch_assoc($resultRealtors);
$realtors = $rowTotalRealtors['total']?? 0;

// registrants
$totalRegistrants = $totalRecords - $realtors;

// Records in the last 7 days
$sqlLast7Days = "SELECT COUNT(*) AS last7Days FROM $table_name WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLast7Days = mysqli_query($connection, $sqlLast7Days);
$rowLast7Days = mysqli_fetch_assoc($resultLast7Days);
$last7DaysRecords = $rowLast7Days['last7Days']?? 0;

$last7DaysRealtors = "SELECT COUNT(*) AS last7DaysRealtors FROM $table_name WHERE $realtorColumn='Yes' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLast7DaysRealtors = mysqli_query($connection, $last7DaysRealtors);
$rowLast7DaysRealtors = mysqli_fetch_assoc($resultLast7DaysRealtors);
$last7DaysRecordsRealtors = $rowLast7DaysRealtors['last7DaysRealtors']?? 0;


// Records in the last to last 7 days
$sqlLastToLast7Days = "SELECT COUNT(*) AS lastToLast7Days FROM $table_name WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLastToLast7Days = mysqli_query($connection, $sqlLastToLast7Days);
$rowLastToLast7Days = mysqli_fetch_assoc($resultLastToLast7Days);
$lastToLast7DaysRecords = $rowLastToLast7Days['lastToLast7Days']?? 0;

$sqlLastToLast7DaysRealtors = "SELECT COUNT(*) AS lastToLast7DaysRealtors FROM $table_name WHERE $realtorColumn='Yes' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultLastToLast7DaysRealtors = mysqli_query($connection, $sqlLastToLast7DaysRealtors);
$rowLastToLast7DaysRealtors = mysqli_fetch_assoc($resultLastToLast7DaysRealtors);
$lastToLast7DaysRecordsRealtors = $rowLastToLast7DaysRealtors['lastToLast7DaysRealtors'] ?? 0;

// Records in the current month
$sqlCurrentMonth = "SELECT COUNT(*) AS currentMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
$resultCurrentMonth = mysqli_query($connection, $sqlCurrentMonth);
$rowCurrentMonth = mysqli_fetch_assoc($resultCurrentMonth);
$currentMonthRecords = $rowCurrentMonth['currentMonth']?? 0;

$sqlCurrentMonthRealtor = "SELECT COUNT(*) AS currentMonthRealtor FROM $table_name WHERE $realtorColumn='Yes' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
$resultCurrentMonthRealtor = mysqli_query($connection, $sqlCurrentMonthRealtor);
$rowCurrentMonthRealtor = mysqli_fetch_assoc($resultCurrentMonthRealtor);
$currentMonthRecordsRealtor = $rowCurrentMonthRealtor['currentMonthRealtor']?? 0;

// Records in the last month
$sqlLastMonth = "SELECT COUNT(*) AS lastMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
$resultLastMonth = mysqli_query($connection, $sqlLastMonth);
$rowLastMonth = mysqli_fetch_assoc($resultLastMonth);
$lastMonthRecords = $rowLastMonth['lastMonth']?? 0;

$sqlLastMonthRealtor = "SELECT COUNT(*) AS lastMonthRealtor FROM $table_name WHERE $realtorColumn='Yes' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
$resultLastMonthRealtor = mysqli_query($connection, $sqlLastMonthRealtor);
$rowLastMonthRealtor = mysqli_fetch_assoc($resultLastMonthRealtor);
$lastMonthRecordsRealtor = $rowLastMonthRealtor['lastMonthRealtor']?? 0;

// Records in the last to last month
$sqlLastToLastMonth = "SELECT COUNT(*) AS lastToLastMonth FROM $table_name WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')";
$resultLastToLastMonth = mysqli_query($connection, $sqlLastToLastMonth);
$rowLastToLastMonth = mysqli_fetch_assoc($resultLastToLastMonth);
$lastToLastMonthRecords = $rowLastToLastMonth['lastToLastMonth']?? 0;

$sqlLastToLastMonthRealtor = "SELECT COUNT(*) AS lastToLastMonthRealtor FROM $table_name WHERE $realtorColumn='Yes' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')";
$resultLastToLastMonthRealtor = mysqli_query($connection, $sqlLastToLastMonthRealtor);
$rowLastToLastMonthRealtor = mysqli_fetch_assoc($resultLastToLastMonthRealtor);
$lastToLastMonthRecordsRealtor = $rowLastToLastMonthRealtor['lastToLastMonthRealtor']?? 0;
