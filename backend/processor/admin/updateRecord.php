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

if (!isset($_GET['id']) || !isset($_GET['table'])) {
    echo "Record or table not specified!";
    exit;
}

$recordId = $_GET['id'];
$recordTable = $_GET['table'];
$sql = "SELECT * FROM $recordTable WHERE id = $recordId";
$result = mysqli_query($connection, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $record = mysqli_fetch_assoc($result);
} else {
    echo "Record not found!";
    exit;
}
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
    <div class="container pt-5">
        <!-- HTML form for updating -->
        <div class="row">

            <div class="col-12 d-flex gap-3 py-3">
                <button class="btn btn-light" onclick="history.back()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                    </svg></button>
                <h3>Update Record</h3>

            </div>
            <!-- error or success messages -->
            <div class="col-12">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="./includes/updateRecordProcess.php" method="POST" class="records-form">
                            <input type="hidden" name="id" value="<?= $recordId ?>">
                            <input type="hidden" name="table" value="<?= $recordTable ?>">
                            <div class="row">
                                <?php
                                // Define fields to hide
                                $hiddenFields = ['created_timestamp', 'ip_address'];
                                // Loop through each field in the record and create an input for each
                                foreach ($record as $field => $value) {
                                    // Skip the 'id' field if you don't want it to be editable
                                    if ($field === 'id' || in_array($field, $hiddenFields)) {
                                        continue;
                                    }

                                    // Set non-editable fields here
                                    $isDisabled = ($field === 'email') ? 'disabled' : ''; // Disable the email field

                                    // Special handling for 'notes' field
                                    if ($field === 'notes') {
                                ?>
                                        <div class="col-12">
                                            <div class="record mb-2">
                                                <label class="form-label" for="<?= $field ?>"><?= ucfirst(str_replace('_', ' ', $field)) ?>:</label>
                                                <textarea class="form-control" rows="10" name="<?= $field ?>" id="<?= $field ?>" class="form-control"><?= htmlspecialchars($value) ?></textarea>
                                            </div>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="col-4">
                                            <div class="record mb-2">
                                                <label class="form-label" for="<?= $field ?>"><?= ucfirst(str_replace('_', ' ', $field)) ?>:</label>
                                                <input class="form-control" type="text" name="<?= $field ?>" id="<?= $field ?>" value="<?= htmlspecialchars($value) ?>" <?= $isDisabled ?>>
                                            </div>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="row">
                                <div class="col-6"><button type="submit" class="btn btn-primary w-100">Update</button></div>
                                <div class="col-6"><button type="button" class="btn btn-light w-100" onclick="history.back()">Cancel</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>