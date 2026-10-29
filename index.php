<?php
// index.php
// Purpose: The home page. Shows quick totals and links to each section.

// 1. CONNECT
require_once __DIR__ . "/includes/db.php";   // Load the shared connection ($conn). __DIR__ is this file's folder.

// 2. GET THE TOTALS
// Without these, the home page would have nothing to show.
$available = $conn->query("SELECT COUNT(*) FROM pets WHERE status = 'Available'")->fetch_row()[0];   // Pets ready for adoption.
$pending   = $conn->query("SELECT COUNT(*) FROM pending_applications")->fetch_row()[0];               // Applications waiting for review (a view).
$adopters  = $conn->query("SELECT COUNT(*) FROM adopters")->fetch_row()[0];                           // Adopters on file.

// 3. SHOW THE PAGE
$page_title = "Home";                              // Tab title.
$active     = "home";                              // Highlight Home in the menu.
require_once __DIR__ . "/includes/header.php";     // Top of the page: menu and CSS.
?>
<h1>Welcome</h1>
<p>Staff use this site to manage pets, adopters and adoption applications.</p>

<div class="cards">                                                         <!-- Three boxes side by side, stacked on a phone. -->
    <a class="card" href="<?= $base ?>/pets/">                              <!-- The whole box links to the section. -->
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
<?php require_once __DIR__ . "/includes/footer.php";   // Bottom of the page. ?>
