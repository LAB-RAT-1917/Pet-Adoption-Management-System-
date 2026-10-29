<?php
// db.php
// Purpose: Opens the connection to the pet_adoption database. Every page loads this file first.
// This file has no closing PHP tag on purpose, so stray spaces after it can't break a page.

// 1. CONNECTION SETTINGS
// Without these, PHP wouldn't know which database to open or which user to log in as.
$host     = "localhost";       // MySQL runs on this computer.
$user     = "petapp";          // The project user, with access to pet_adoption only.
$pass     = "PetAdopt#143";    // That user's password. The user only works on this computer.
$database = "pet_adoption";    // The database that schema.sql builds.
$port     = 3306;              // MySQL's port.

// 2. CONNECT
// Without this, no page could read or save anything.
try {
    $conn = new mysqli($host, $user, $pass, $database, $port);   // Open the connection. Every page uses $conn.
    $conn->set_charset("utf8mb4");                                // Read and save names like Beyoncé correctly.
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());       // Stop the page and show why, instead of a blank page.
}
