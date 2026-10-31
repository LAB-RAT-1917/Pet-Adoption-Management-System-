<?php
// delete.php
// Project: Pet Adoption Management System (CIS 344 Group Project).
// Purpose: Deletes a pet that has no applications, like one entered by mistake (Pets requirement #4).
// The Delete button on the pet list sends the pet's ID here. This page shows nothing itself. It goes back to the list with a message.

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";      // The shared connection ($conn).

// 2. ONLY ACCEPT THE DELETE BUTTON
// A delete must come from the button's form (POST), never from a link or a typed address (GET).
// Without this, just opening delete.php in the browser could delete a pet.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");                 // Back to the list. Nothing is deleted.
    exit;                                          // Stop here, so nothing below runs.
}

// 3. READ THE PET ID
$id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);   // A whole number from 1 up, or false.
if ($id === false) {
    header("Location: index.php?done=missing");    // Not a real ID, so there's no pet to delete.
    exit;
}

// 4. CHECK FOR APPLICATIONS (Business rules #17 and #28)
// A pet with any application, even a rejected one, is part of the store's history, so it stays.
// Without this check, staff would get a MySQL error instead of a clear message.
$stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE pet_id = ?");
$stmt->bind_param("i", $id);                       // i = whole number.
$stmt->execute();
$applications = $stmt->get_result()->fetch_row()[0];   // How many applications point to this pet.
if ($applications > 0) {
    header("Location: index.php?done=in-use");     // The list says the pet has applications, so it can't be deleted.
    exit;
}

// 5. DELETE THE PET
// MySQL guards Business rule #17 too. The foreign key (ON DELETE RESTRICT) refuses with Error 1451
// if an application was added after the check above. The catch turns that error into the same message.
try {
    $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();                              // Delete the pet.
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1451) {                  // 1451 = a foreign key blocked the delete.
        header("Location: index.php?done=in-use");
        exit;
    }
    die("The pet couldn't be deleted: " . $e->getMessage());   // Any other error is a real problem, so show it.
}

// 6. GO BACK TO THE LIST
// affected_rows counts the rows deleted. It's 1 if the pet was there, or 0 if it was already gone.
header("Location: index.php?done=" . ($stmt->affected_rows === 1 ? "deleted" : "missing"));
exit;
