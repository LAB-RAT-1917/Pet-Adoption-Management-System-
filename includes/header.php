<?php
// header.php
// Purpose: The top of every page: page title, CSS, store name and menu.
// How to use: set $page_title and $active, then load this file (see index.php).

// 1. SETTINGS
// Without these, links would break on pages inside folders like pets/.
$base       = "/pet-adoption-system";      // The project folder in htdocs. Every link starts here, so it works from any page.
$page_title = $page_title ?? "Home";       // The tab title. "Home" if the page didn't set one.
$active     = $active ?? "home";           // Which menu link to highlight. Home if the page didn't say.

// 2. MENU LINKS
// Without this list, every page would need its own copy of the menu.
$menu = [                                  // Each entry: menu name => [link text, address].
    "home"         => ["Home",         "$base/"],
    "pets"         => ["Pets",         "$base/pets/"],
    "adopters"     => ["Adopters",     "$base/adopters/"],
    "applications" => ["Applications", "$base/applications/"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                    <!-- Lets names like Beyoncé show correctly. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">      <!-- Makes the page fit phone screens. -->
    <title><?= htmlspecialchars($page_title) ?> | Pet Adoption Store</title>  <!-- Tab title, like "Pets | Pet Adoption Store". -->
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">                 <!-- One stylesheet for every page. -->
</head>
<body>
<header class="site-header">
    <a class="logo" href="<?= $base ?>/">Pet Adoption Store</a>              <!-- Store name, links back home. -->
    <nav class="menu">
        <?php foreach ($menu as $key => [$text, $link]):   // Make one link per menu entry. ?>
            <a href="<?= $link ?>"<?= $key === $active ? ' class="active"' : '' ?>><?= $text ?></a>
            <?php // The link for the current section gets class="active", so the CSS can highlight it. ?>
        <?php endforeach;   // End of the menu links. ?>
    </nav>
</header>
<main class="page">
