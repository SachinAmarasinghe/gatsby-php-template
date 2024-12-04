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
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($realtors) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($totalRegistrants) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="info_pill">
                    <span>Last 7 Days</span>
                    <h2><?= $last7DaysRecords ?></h2>
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($last7DaysRecordsRealtors) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($last7DaysRecords - $last7DaysRecordsRealtors ) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="info_pill">
                    <span>Last to Last 7 Days</span>
                    <h2><?= $lastToLast7DaysRecords ?></h2>
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($lastToLast7DaysRecordsRealtors) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($lastToLast7DaysRecords - $lastToLast7DaysRecordsRealtors ) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="info_pill">
                    <span><?= $currentMonthName ?></span>
                    <h2><?= $currentMonthRecords ?></h2>
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($currentMonthRecordsRealtor) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($currentMonthRecords - $currentMonthRecordsRealtor ) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="info_pill">
                    <span><?= $lastMonthName ?></span>
                    <h2><?= $lastMonthRecords ?></h2>
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($lastMonthRecordsRealtor) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($lastMonthRecords - $lastMonthRecordsRealtor ) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="info_pill">
                    <span><?= $lastToLastMonthName ?></span>
                    <h2><?= $lastToLastMonthRecords ?></h2>
                    <div class="inner-data">
                        <div>
                            <span>Realtors</span>
                            <h2><?= number_format($lastToLastMonthRecordsRealtor) ?></h2>
                        </div>
                        <div>
                            <span>Registrants</span>
                            <h2><?= number_format($lastToLastMonthRecords - $lastToLastMonthRecordsRealtor ) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 text-right pt-3">
            <a href="<?= $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'] ?>&download=true" class="btn btn-success big"><i class="fa-solid fa-file-csv"></i> Export to CSV File</a>
        </div>
    </div>

<?php
}
?>