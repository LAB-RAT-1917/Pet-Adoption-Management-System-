<?php
// delete.php
// Purpose: Deletes a pet that has no applications, like one entered by mistake (Pets requirement #4).
// The Delete button on the pet list sends the pet's ID here. This page shows nothing itself. It goes back to the list with a message.

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";

// 2. ONLY ACCEPT THE DELETE BUTTON
// A delete must come from the button's form (POST), never from a link or a typed address (GET).
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");                 // Back to the list. Nothing is deleted.
    exit;
}

// 3. READ THE PET ID
$id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);   // A whole number from 1 up, or false.
if ($id === false) {
    header("Location: index.php?done=missing");
    exit;
}

// 4. CHECK FOR APPLICATIONS (Business rules #17 and #28)
// A pet with any application, even a rejected one, is part of the store's history, so it stays.
$stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE pet_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_row()[0];
if ($applications > 0) {
    header("Location: index.php?done=in-use");
    exit;
}

// 5. DELETE THE PET
// MySQL guards Business rule #17 too. The foreign key (ON DELETE RESTRICT) refuses with Error 1451
// if an application was added after the check above. The catch turns that error into the same message.
try {
    $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1451) {                  // 1451 = a foreign key blocked the delete.
        header("Location: index.php?done=in-use");
        exit;
    }
    die("The pet couldn't be deleted: " . $e->getMessage());
}

// 6. GO BACK TO THE LIST
// affected_rows is 1 if the pet was there, or 0 if it was already gone.
header("Location: index.php?done=" . ($stmt->affected_rows === 1 ? "deleted" : "missing"));
exit;
