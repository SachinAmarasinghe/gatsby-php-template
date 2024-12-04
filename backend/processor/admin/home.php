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
include 'includes/helpers.php';
?>

<?php include("./includes/meta.php") ?>
<title>Admin Panel, Download Form Data</title>

</head>

<body class="admin-page">
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="https://sean.ca/images/sean-logo-dark-hr.png" alt="" height="24" class="d-inline-block align-text-top">
            </a>
            <div class="d-flex align-items-center gap-2">
                <div class="nav-item">
                    <a href="/processor/admin/upload-records.php" class="nav-link">Upload records</a>
                </div>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-right-from-bracket"></i> LOG OUT</a>
            </div>
        </div>
    </nav>
    <div class="container pt-3">
        <!-- table selector  -->
        <?php include("includes/tableSelector.php") ?>

        <!-- data summary  -->
        <?php include("includes/dataSummary.php") ?>
    </div>

    <?php
    if ($table_name && $rowCount) {
    ?>
        <div class="container">
            <div class="row mb-2">
                <!-- Search Input -->
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                </div>
                <div class="col-md-8 d-flex justify-content-end">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination" id="paginationControls"></ul>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div id="tblData" class="scroll-table">
                        <table class="custom-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-nowrap" scope="col">S. No.</th>
                                    <?php
                                    if (!empty($table_name) && $result) {
                                        if (mysqli_num_rows($result) > 0) {
                                            foreach ($adminTables["$table_name"]['fields'] as $thead) {
                                    ?>
                                                <th scope="col">
                                                    <a href="<?= $_SERVER["PHP_SELF"] ?>?table=<?= $table_name ?>&orderby=<?= $thead  ?>&sortby=<?= ((isset($_GET['sortby']) && $_GET['sortby'] == 'asc') ? 'desc' : 'asc') ?>" class=""><?= ucfirst(str_replace('_', ' ', $thead)) ?> </a>
                                                </th>
                                            <?php
                                            }
                                            ?>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                            $count = 1;
                                            while ($row = mysqli_fetch_array($result)) {
                                ?>
                                    <tr onclick="window.location.href='updateRecord.php?id=<?= $row['id'] ?>&table=<?= $table_name ?>'">
                                        <td><?= $count++ ?></td>
                                        <?php
                                                foreach ($adminTables["$table_name"]['fields'] as $td) {
                                        ?>
                                            <td><?= $row["$td"] ?></td>
                                        <?php
                                                }
                                        ?>
                                    </tr>
                                <?php
                                            }
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php
                                        }
                                    }
            ?>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const rowsPerPage = 30; // Number of rows to show per page
                let currentPage = 1;

                const tableBody = document.getElementById("tableBody");
                const rows = Array.from(tableBody.getElementsByTagName("tr"));
                const paginationControls = document.getElementById("paginationControls");

                function renderPagination() {
                    const totalPages = Math.ceil(rows.length / rowsPerPage);
                    paginationControls.innerHTML = "";

                    // Create Previous button
                    const prevBtn = document.createElement("li");
                    prevBtn.className = "page-item";
                    prevBtn.innerHTML = `<a class="page-link" href="#">Previous</a>`;
                    prevBtn.onclick = () => {
                        if (currentPage > 1) goToPage(currentPage - 1);
                    };
                    paginationControls.appendChild(prevBtn);

                    // Create page number buttons
                    for (let i = 1; i <= totalPages; i++) {
                        const pageItem = document.createElement("li");
                        pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
                        pageItem.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageItem.onclick = () => goToPage(i);
                        paginationControls.appendChild(pageItem);
                    }

                    // Create Next button
                    const nextBtn = document.createElement("li");
                    nextBtn.className = "page-item";
                    nextBtn.innerHTML = `<a class="page-link" href="#">Next</a>`;
                    nextBtn.onclick = () => {
                        if (currentPage < totalPages) goToPage(currentPage + 1);
                    };
                    paginationControls.appendChild(nextBtn);
                }

                function goToPage(page) {
                    currentPage = page;
                    const start = (page - 1) * rowsPerPage;
                    const end = start + rowsPerPage;

                    rows.forEach((row, index) => {
                        row.style.display = (index >= start && index < end) ? "" : "none";
                    });

                    // Re-render pagination to update active page
                    renderPagination();
                }

                function filterTable() {
                    const searchTerm = document.getElementById("searchInput").value.toLowerCase();

                    rows.forEach(row => {
                        const cells = row.getElementsByTagName("td");
                        const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(" ");
                        row.style.display = rowText.includes(searchTerm) ? "" : "none";
                    });

                    // Reset pagination if search input is cleared
                    if (!searchTerm) {
                        goToPage(1); // Go back to the first page when search is cleared
                    }
                }

                // Initial render
                renderPagination();
                goToPage(currentPage);

                // Event listeners
                document.getElementById("searchInput").addEventListener("input", filterTable);
            });
        </script>
    <?php
    } else if ($table_name) {
    ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="text-danger">No records matching your query were found. Please check your query!</h5>
                    <small>Error Description: Table does not exist OR no records in the table</small>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="text-danger">Select a table from above</h5>
                </div>
            </div>
        </div>
    <?php
    }
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