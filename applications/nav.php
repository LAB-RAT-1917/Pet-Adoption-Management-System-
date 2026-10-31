<?php
// applications/nav.php
// Purpose: The small tab menu shared by the Applications pages (Pending, Approved, Rejected, New).
// How to use: set $tab before header.php, then load this file right after header.php.
// $base comes from header.php.

$tabs = [                                                // Each entry: tab name => [link text, file].
    "pending"  => ["Pending",           "index.php"],
    "approved" => ["Approved",          "approved.php"],
    "rejected" => ["Rejected",          "rejected.php"],
    "new"      => ["+ New Application", "new.php"],
];
?>
<nav class="tabs">
    <?php foreach ($tabs as $key => [$text, $file]): ?>
        <a href="<?= $base ?>/applications/<?= $file ?>"<?= $key === $tab ? ' class="active"' : '' ?>><?= $text ?></a>
    <?php endforeach; ?>
</nav>
