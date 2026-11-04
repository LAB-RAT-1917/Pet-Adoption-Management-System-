<?php
// index.php
// Purpose: The home page. Shows quick totals and links to each section.

// 1. CONNECT
require_once __DIR__ . "/includes/db.php";

// 2. GET THE TOTALS
$available = $conn->query("SELECT COUNT(*) FROM pets WHERE status = 'Available'")->fetch_row()[0];
$pending   = $conn->query("SELECT COUNT(*) FROM pending_applications")->fetch_row()[0];               // A view from schema.sql.
$adopters  = $conn->query("SELECT COUNT(*) FROM adopters")->fetch_row()[0];

// 3. SHOW THE PAGE
$page_title = "Home";
$active     = "home";
require_once __DIR__ . "/includes/header.php";
?>
<h1>Welcome</h1>
<p>Staff use this site to manage pets, adopters and adoption applications.</p>

<div class="cards">
    <a class="card" href="<?= $base ?>/pets/">
        <span class="card-number"><?= $available ?></span>
        <span class="card-label">Pets available</span>
    </a>
    <a class="card" href="<?= $base ?>/applications/">
        <span class="card-number"><?= $pending ?></span>
        <span class="card-label">Applications waiting for review</span>
    </a>
    <a class="card" href="<?= $base ?>/adopters/">
        <span class="card-number"><?= $adopters ?></span>
        <span class="card-label">Adopters on file</span>
    </a>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
