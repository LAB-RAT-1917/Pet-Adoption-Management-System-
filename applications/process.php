<?php
// applications/process.php
// Purpose: Approves or rejects one Pending application. It has no page of its own:
// it saves the decision, then sends staff back to the Pending list with a message.
//   Approve: the application becomes Approved, and the pet becomes Adopted.
//   Reject:  the application becomes Rejected with a staff reason (Business rules #26 and #27),
//            and the pet becomes Available again.
// The application and the pet change together, or not at all (Business rule #16).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Shared connection ($conn).

// Sends staff back to the Pending list with a message, then stops this file.
function back_to_pending(string $msg, string $type = "success"): void
{
    header("Location: index.php?type=" . $type . "&msg=" . urlencode($msg));
    exit;
}

// 2. CHECK THE REQUEST
// Only the Approve and Reject buttons may change data, never a typed-in link.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    back_to_pending("Please use the Approve or Reject buttons.", "error");
}

$action         = $_POST["action"] ?? "";
$application_id = filter_var($_POST["application_id"] ?? "", FILTER_VALIDATE_INT);   // Whole number, or false.
$reason_id      = filter_var($_POST["reason_id"] ?? "", FILTER_VALIDATE_INT);

if ($application_id === false || !in_array($action, ["approve", "reject"], true)) {
    back_to_pending("That request was not valid.", "error");
}

if ($action === "reject") {
    // The reason must be one staff can choose after review (Business rules #26 and #27).
    $stmt = $conn->prepare("SELECT reason_id FROM rejection_reasons WHERE reason_id = ? AND rule_number IN (26, 27)");
    $stmt->bind_param("i", $reason_id);
    $stmt->execute();
    if ($reason_id === false || !$stmt->get_result()->fetch_row()) {
        back_to_pending("Please choose a reason before rejecting.", "error");
    }
}

// 3. SAVE THE DECISION AS ONE TRANSACTION (Business rule #16)
$in_transaction = false;
try {
    $conn->begin_transaction();
    $in_transaction = true;

    // Lock this application until we finish, so two staff can't decide it at the same time.
    $stmt = $conn->prepare("SELECT pet_id, status FROM applications WHERE application_id = ? FOR UPDATE");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $application = $stmt->get_result()->fetch_assoc();

    if (!$application || $application["status"] !== "Pending") {
        $conn->rollback();
        back_to_pending("Application #$application_id is not pending anymore.", "error");
    }
    $pet_id = (int) $application["pet_id"];

    if ($action === "approve") {
        $stmt = $conn->prepare("UPDATE applications SET status = 'Approved' WHERE application_id = ?");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE pets SET status = 'Adopted' WHERE pet_id = ?");
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();

        $msg = "Application #$application_id was approved. The pet is now Adopted.";
    } else {
        $stmt = $conn->prepare("UPDATE applications SET status = 'Rejected', rejection_reason_id = ? WHERE application_id = ?");
        $stmt->bind_param("ii", $reason_id, $application_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE pets SET status = 'Available' WHERE pet_id = ? AND status = 'Pending'");
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();

        $msg = "Application #$application_id was rejected. The pet is Available again.";
    }

    $conn->commit();   // Both changes are saved together.
    $in_transaction = false;
    back_to_pending($msg);
} catch (mysqli_sql_exception $e) {
    if ($in_transaction) {
        $conn->rollback();   // Neither change is saved.
    }
    back_to_pending("Something went wrong, so nothing was changed. Please try again.", "error");
}
