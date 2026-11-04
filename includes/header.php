<?php
// header.php
// Purpose: The top of every page: page title, CSS, store name and menu.
// How to use: set $page_title and $active, then load this file (see index.php).

// 1. SETTINGS
$base       = "/pet-adoption-system";      // The project folder in htdocs. Every link starts here, so it works from any page.
$page_title = $page_title ?? "Home";
$active     = $active ?? "home";           // Which menu link to highlight.

// 2. MENU LINKS
$menu = [                                  // menu name => [link text, address]
    "home"         => ["Home",         "$base/"],
    "pets"         => ["Pets",         "$base/pets/"],
    "adopters"     => ["Adopters",     "$base/adopters/"],
    "applications" => ["Applications", "$base/applications/"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">      <!-- Makes the page fit phone screens. -->
    <title><?= htmlspecialchars($page_title) ?> | Pet Adoption Store</title>
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="<?= $base ?>/">Pet Adoption Store</a>
    <nav class="menu">
        <?php foreach ($menu as $key => [$text, $link]): ?>
            <a href="<?= $link ?>"<?= $key === $active ? ' class="active"' : '' ?>><?= $text ?></a>
        <?php endforeach; ?>
    </nav>
</header>
<main class="page">
