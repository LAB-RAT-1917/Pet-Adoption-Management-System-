<?php
// edit.php
// Purpose: The form to edit an adopter's contact details (Adopters requirement #2).
// The ID and the date joined never change (Business rule #12).

// 1. CONNECT AND LOAD THE CHECKS
require_once __DIR__ . "/../includes/db.php";      // The shared connection ($conn).
require_once __DIR__ . "/check-adopter.php";       // check_adopter_form() and email_taken(), shared with add.php.

// 2. FIND THE ADOPTER
// Without this, the page wouldn't know who to edit. The ID comes from the address, like edit.php?id=1.
// Musicians have negative IDs, like -110989116, so negatives are allowed.
$id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);   // A whole number, or false if it isn't one.
$stmt = $conn->prepare(
    "SELECT adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined
     FROM adopters WHERE adopter_id = ?"
);
$stmt->bind_param("i", $id);                       // i = whole number.
$stmt->execute();
$saved = $stmt->get_result()->fetch_assoc();       // The adopter as they are now, or null if there's no one with that ID.

// 3. START WITH THE SAVED VALUES
$adopter = $saved;                                 // Fill the form with what's saved.
$errors  = [];                                     // No problems until the form is sent.

// 4. SAVE THE CHANGES WHEN THE FORM IS SENT
// Without this, clicking "Save changes" would just reload the page.
if ($saved && $_SERVER["REQUEST_METHOD"] === "POST") {
    [$adopter, $errors] = check_adopter_form($_POST);   // Same checks as the Add page.

    if (!$errors && email_taken($conn, $adopter["email"], $id)) {   // $id skips this adopter, so keeping their own email is fine.
        $errors[] = "Another adopter already uses that email (Business rule #7).";
    }

    if (!$errors) {                                // Only save when there are no problems.
        try {
            $stmt = $conn->prepare(
                "UPDATE adopters
                 SET first_name = ?, last_name = ?, phone = ?, email = ?, address = ?, date_of_birth = ?
                 WHERE adopter_id = ?"             // adopter_id and date_joined are left out, so they never change.
            );
            $stmt->bind_param("ssssssi",           // s = string, i = whole number.
                $adopter["first_name"], $adopter["last_name"], $adopter["phone"], $adopter["email"], $adopter["address"], $adopter["date_of_birth"], $id);
            $stmt->execute();                      // Save the changes.

            header("Location: index.php?done=updated");   // Back to the list, which shows "The adopter was updated."
            exit;
        } catch (mysqli_sql_exception $e) {
            // 1062 = the email is already used. It can only happen if someone else saved that email at the same moment.
            if ($e->getCode() !== 1062) {
                die("The adopter couldn't be updated: " . $e->getMessage());   // Any other error is a real problem, so show it.
            }
            $errors[] = "Another adopter already uses that email (Business rule #7).";
        }
    }
}

// 5. SHOW THE PAGE
$page_title = $saved ? "Edit " . $saved["first_name"] . " " . $saved["last_name"] : "Adopter not found";   // Tab title.
$active     = "adopters";                          // Highlight Adopters in the menu.
require_once __DIR__ . "/../includes/header.php";  // Top of the page.
?>
<?php if (!$saved): ?>                              <!-- Wrong or missing ID in the address. -->
    <h1>Adopter not found</h1>
    <p class="message error">There's no adopter with that ID.</p>
    <p><a href="index.php">Back to the adopter list</a></p>
<?php else: ?>
    <h1>Edit <?= htmlspecialchars($saved["first_name"] . " " . $saved["last_name"]) ?></h1>
    <p class="note">Adopter ID <?= $saved["adopter_id"] ?> &middot; Joined <?= date("M j, Y", strtotime($saved["date_joined"])) ?></p>

    <form method="post">                            <!-- Sends the form to this same address, so the ID stays. -->
        <?php require __DIR__ . "/adopter-form.php";   // The error box and adopter fields, shared with add.php. ?>

        <div class="form-buttons">
            <button class="button" type="submit">Save changes</button>
            <a href="index.php">Cancel</a>             <!-- Back to the list, with nothing saved. -->
        </div>
    </form>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php";   // Bottom of the page. ?>
