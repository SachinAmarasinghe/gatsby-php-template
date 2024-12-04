<?php
//echo "before init<br>";
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once('config.php');


$table_array[] = "sean_registration";
$table_array[] = "sean_283_rainwater_registration";
$table_array[] = "sean_bracebridge_registration";


$white_list_ips[] = "142.112.118.55"; // office 1
$white_list_ips[] = "142.112.107.27"; // office 2
$white_list_ips[] = "65.93.118.50"; // rob
$white_list_ips[] = "184.145.24.139"; // Aayushi
$white_list_ips[] = "170.52.107.79"; // Bhanu
$white_list_ips[] = "192.168.0.95"; // Kati
$white_list_ips[] = "99.237.35.234"; // Sachin


function close_db_connection()
{
	if (isset($connection)) {
		mysqli_close($connection);
		unset($connection);
	}
}

function getUserIpAddrNew()
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


$ip = getUserIpAddrNew();


// check if safe ip address
if (!in_array($ip, $white_list_ips)) {
	die;
}


// ---------------------------------------------------------------------------
// create excel csv file starts
// ---------------------------------------------------------------------------


if (isset($_GET["tbl"]) && in_array($_GET["tbl"], $table_array)) {

	$fp = fopen('php://output', 'w');

	if ($fp) {

		$currentTableName = $_REQUEST["tbl"];
		$currentName = $_REQUEST["name"];

		//$sqlRegistrants = "SELECT * FROM  bas_registrants $whereClause ORDER BY id $limit";
		$sqlRegistrants = "SELECT * FROM  $currentTableName ORDER BY id";

		$result_sqlRegistrants = mysqli_query($connection, $sqlRegistrants);

		$numfields = mysqli_num_fields($result_sqlRegistrants);

		$feildArray;

		for ($i = 0; $i < $numfields; $i++) // Header
		{
			$colObj = mysqli_fetch_field_direct($result_sqlRegistrants, $i);
			$col = $colObj->name;
			$myFieldName = $colObj->name;
			$myFieldName = str_replace("_", " ", $myFieldName);
			$myFieldName = ucwords($myFieldName);
			$headers[] = $myFieldName;
		}

		$fileName = "registrants_" . $_GET["tbl"] . "_" . time() . ".csv";

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		fputcsv($fp, $headers);
		$csv_output = "";

		while ($row = mysqli_fetch_row($result_sqlRegistrants)) // Data
		{
			$currentRowArray = array();
			for ($i = 0; $i < $numfields; $i++) {
				$currentRowArray[] = $row[$i];
			}
			$rowArray = $currentRowArray;
			fputcsv($fp, $rowArray);
		}
	}
	close_db_connection();
	die;
}


// ---------------------------------------------------------------------------
// create excel csv file starts
// ---------------------------------------------------------------------------




$start_date_days_offset_from_today = 1; //1;
$total_days_range_offset = 1; //1;



//Live four hours
$tomorrow_date_mktime = mktime(0, 0, 0, date("n"), date("j") + $start_date_days_offset_from_today, date("Y"));


$reminer_date_mktime = $tomorrow_date_mktime; // for live site

$today_year = date("Y", $reminer_date_mktime);
$today_month = date("n", $reminer_date_mktime);
$today_day = date("j", $reminer_date_mktime);

$todayfromTime = mktime(0, 0, 0, $today_month, $today_day - 0, $today_year);
$totadytoTime = mktime(0, 0, 0, $today_month, $today_day - 1, $today_year);

$last7fromTime = mktime(0, 0, 0, $today_month, $today_day - 1, $today_year);
$last7toTime = mktime(0, 0, 0, $today_month, $today_day - 8, $today_year);

$lastToLast7fromTime = mktime(0, 0, 0, $today_month, $today_day - 8, $today_year);
$lastToLast7toTime = mktime(0, 0, 0, $today_month, $today_day - 15, $today_year);


$last30fromTime = mktime(0, 0, 0, $today_month, $today_day - 1, $today_year);
$last30toTime = mktime(0, 0, 0, $today_month, $today_day - 31, $today_year);

$lastToLast30fromTime = mktime(0, 0, 0, $today_month, $today_day - 31, $today_year);
$lastToLast30toTime = mktime(0, 0, 0, $today_month, $today_day - 61, $today_year);



function getTimeStampColumnName($passTableName)
{

	global $connection;

	//created_at
	//" . $timeStampColumn . "

	$agent_filed_name = "";


	$query = "SELECT * FROM " . $passTableName . " LIMIT 1;";




	if ($result = mysqli_query($connection, $query)) {

		$findAgent   = 'created';
		$findRealtor   = 'confirm';

		/* Get field information for all columns */
		while ($finfo = $result->fetch_field()) {

			$filed_name = strtolower($finfo->name);


			//echo("Name: " . $finfo->name . "<br>");


			$posAgent = strpos($filed_name, $findAgent);
			$posfindRealtor = strpos($filed_name, $findRealtor);

			// echo("posAgent: " . $posAgent . "<br>");
			// echo("posfindRealtor: " . $posfindRealtor . "<br>");


			if ($posAgent !== false) {
				// found agent
				//echo 'found created' . " " . $filed_name . "<br>";
				$agent_filed_name = $filed_name;
				break;
			}

			if ($posfindRealtor !== false) {
				// found agent
				//echo 'found confirm' . " " . $filed_name . "<br>";
				$agent_filed_name = $filed_name;
				break;
			}


			// printf("Table:    %s\n", $finfo->table);
			// printf("max. Len: %d\n", $finfo->max_length);
			// printf("Flags:    %d\n", $finfo->flags);
			// printf("Type:     %d\n\n", $finfo->type);
		}

		$result->close();
	}


	if (strlen($agent_filed_name) > 2) {
		return $agent_filed_name;
	} else {
		return '';
	}
}

function getGrandTotalAgetns($passTableName)
{

	global $connection;

	$agent_filed_name = "";


	$query = "SELECT * FROM " . $passTableName . " LIMIT 1;";

	if ($result = mysqli_query($connection, $query)) {

		$findAgent   = 'agent';
		$findRealtor   = 'realtor';

		/* Get field information for all columns */
		while ($finfo = $result->fetch_field()) {

			$filed_name = strtolower($finfo->name);


			//echo("Name: " . $finfo->name . "<br>");


			$posAgent = strpos($filed_name, $findAgent);
			$posfindRealtor = strpos($filed_name, $findRealtor);

			// echo("posAgent: " . $posAgent . "<br>");
			// echo("posfindRealtor: " . $posfindRealtor . "<br>");


			if ($posAgent !== false) {
				// found agent
				$agent_filed_name = $filed_name;
				break;
			}

			if ($posfindRealtor !== false) {
				// found agent
				$agent_filed_name = $filed_name;
				break;
			}


			// printf("Table:    %s\n", $finfo->table);
			// printf("max. Len: %d\n", $finfo->max_length);
			// printf("Flags:    %d\n", $finfo->flags);
			// printf("Type:     %d\n\n", $finfo->type);
		}

		$result->close();
	}


	if (strlen($agent_filed_name) > 2) {
		$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE LOWER($agent_filed_name) LIKE '%y%';";

		$result_sqlRegistrants = mysqli_query($connection, $mysql);

		while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
			return $row_count_result_access_codes_sql["total"];
		}
	} else {
		return 'N/A';
	}
}

function getGrandTotal($passTableName)
{

	global $last7fromTime, $last7toTime, $connection;

	$from = $last7fromTime;
	$to = $last7toTime;

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " ;";

	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}

function getToday($passTableName)
{

	global $todayfromTime, $totadytoTime, $connection;


	$from = $todayfromTime;
	$to = $totadytoTime;

	$timeStampColumn = getTimeStampColumnName($passTableName);

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE UNIX_TIMESTAMP(`" . $timeStampColumn . "`) < $from AND UNIX_TIMESTAMP(`" . $timeStampColumn . "`) > $to";





	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}

function getLast7($passTableName)
{

	global $last7fromTime, $last7toTime, $connection;


	$from = $last7fromTime;
	$to = $last7toTime;

	$timeStampColumn = getTimeStampColumnName($passTableName);

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE UNIX_TIMESTAMP(`" . $timeStampColumn . "`) < $from AND UNIX_TIMESTAMP(`" . $timeStampColumn . "`) > $to";

	// echo $mysql;

	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}

function getLastToLast7($passTableName)
{

	global $lastToLast7fromTime, $lastToLast7toTime, $connection;

	$from = $lastToLast7fromTime;
	$to = $lastToLast7toTime;

	$timeStampColumn = getTimeStampColumnName($passTableName);

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE UNIX_TIMESTAMP(`" . $timeStampColumn . "`) < $from AND UNIX_TIMESTAMP(`" . $timeStampColumn . "`) > $to";


	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}

function getLast30($passTableName)
{

	global $last30fromTime, $last30toTime, $connection;

	$from = $last30fromTime;
	$to = $last30toTime;

	$timeStampColumn = getTimeStampColumnName($passTableName);

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE UNIX_TIMESTAMP(`" . $timeStampColumn . "`) < $from AND UNIX_TIMESTAMP(`" . $timeStampColumn . "`) > $to";


	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}

function getLastToLast30($passTableName)
{

	global $lastToLast30fromTime, $lastToLast30toTime, $connection;

	$from = $lastToLast30fromTime;
	$to = $lastToLast30toTime;

	$timeStampColumn = getTimeStampColumnName($passTableName);

	$mysql = "SELECT count(id) as total FROM " . $passTableName . " WHERE UNIX_TIMESTAMP(`" . $timeStampColumn . "`) < $from AND UNIX_TIMESTAMP(`" . $timeStampColumn . "`) > $to";


	$result_sqlRegistrants = mysqli_query($connection, $mysql);

	while ($row_count_result_access_codes_sql = mysqli_fetch_array($result_sqlRegistrants)) {
		return $row_count_result_access_codes_sql["total"];
	}
}






// echo 'Agents : ' . getGrandTotalAgetns('beechwood_registration_test') . '<br>';
?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Bootstrap demo</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>

<body>



	<div class="container-fluid">
		<div class="row">
			<div class="col">


				<h1 class="text-center mt-4" style="color: red;"> NEVER SEND THIS LINK TO CLIENT </h1>

				<h2 class="text-center">
					<?php

					echo 'IP Address ' . $ip;

					?>
				</h2>

				<table class="table  table-striped">
					<tr>
						<th>Table Name</th>
						<th>Download Link</th>
						<th>Grand Total</th>
						<th>Total Agents</th>
						<th>Todays</th>
						<th>Last 7 Days</th>
						<th>Last to Last 7 Days</th>
						<th>Last 30 Days</th>
						<th>Last to Last 30 Days</th>
					</tr>


					<?php





					foreach ($table_array as $form_table_name):

					?>
						<tr>
							<td><?php echo $form_table_name; ?></td>
							<td><a href="stats.php?name=<?php echo $form_table_name; ?>&tbl=<?php echo $form_table_name; ?>">Download CSV File</a></td>
							<td><?php echo getGrandTotal($form_table_name); ?></td>
							<td><?php echo getGrandTotalAgetns($form_table_name); ?></td>
							<td><?php echo getToday($form_table_name); ?></td>
							<td><?php echo getLast7($form_table_name); ?></td>
							<td><?php echo getLastToLast7($form_table_name); ?></td>
							<td><?php echo getLast30($form_table_name); ?></td>
							<td><?php echo getLastToLast30($form_table_name); ?></td>
						</tr>

					<?php
					endforeach;

					close_db_connection();

					?>
				</table>


			</div>
		</div>
	</div>


	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>

</html>