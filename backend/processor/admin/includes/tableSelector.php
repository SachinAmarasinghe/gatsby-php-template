<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs">
            <?php
            // Get the current table parameter from the URL
            $currentTable = $_GET['table'] ?? '';

            // Flag to apply 'active' to the first tab if no 'table' parameter is provided
            $isFirst = empty($currentTable);

            foreach ($adminTables as $key => $table) {
                // Set 'active' if this tab matches the current table OR if it's the first and no table parameter is provided
                $isActive = ($currentTable === $table['table_name'] || $isFirst) ? 'active' : '';
            ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive ?>"
                        href="/processor/admin/home.php?table=<?= $table['table_name'] ?>">
                        <?= $table['title'] ?>
                    </a>
                </li>
            <?php
                $isFirst = false; // After the first iteration, ensure no other tabs default to active
            }
            ?>
        </ul>


    </div>
</div>