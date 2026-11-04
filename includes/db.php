<?php
// db.php
// Purpose: Opens the connection to the pet_adoption database. Every page loads this file first.
// This file has no closing PHP tag on purpose, so stray spaces after it can't break a page.

// 1. CONNECTION SETTINGS
$host     = "localhost";
$user     = "petapp";          // The project user, with access to pet_adoption only.
$pass     = "PetAdopt#143";    // petapp only accepts connections from this computer.
$database = "pet_adoption";
$port     = 3306;

// 2. CONNECT
try {
    $conn = new mysqli($host, $user, $pass, $database, $port);
    $conn->set_charset("utf8mb4");                                // Reads and saves names like Beyoncé correctly.
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());       // Shows why, instead of a blank page.
}
