<?php
// add.php
// Purpose: The form to add a new adopter (Adopters requirement #1).
// The new adopter gets the largest ID + 1, and MySQL fills in the date joined (Business rule #12).

// 1. CONNECT AND LOAD THE CHECKS
require_once __DIR__ . "/../includes/db.php";      // The shared connection ($conn).
require_once __DIR__ . "/check-adopter.php";       // check_adopter_form() and email_taken(), shared with edit.php.

// 2. WORK OUT THE NEXT ID (Business rule #12)
// adopter_id isn't numbered automatically, because the store assigns it. So the page takes the largest ID + 1, like 16 + 1 = 17.
// Without this, staff would have to look up and type an ID that isn't taken yet.
function next_adopter_id(mysqli $conn): int
{
    $sql = "SELECT COALESCE(MAX(adopter_id), 0) + 1 FROM adopters";   // COALESCE gives 1 when there are no adopters yet.
    return (int) $conn->query($sql)->fetch_row()[0];                  // The one number the query gives back.
}

// 3. START WITH AN EMPTY FORM
$adopter = ["first_name" => "", "last_name" => "", "phone" => "", "email" => "", "address" => "", "date_of_birth" => ""];   // "" = not filled in yet.
$errors  = [];                                     // No problems until the form is sent.

// 4. SAVE THE ADOPTER WHEN THE FORM IS SENT
// Without this, clicking "Add adopter" would just reload the page and save nothing.
if ($_SERVER["REQUEST_METHOD"] === "POST") {       // POST means the form was sent.
    [$adopter, $errors] = check_adopter_form($_POST);   // Clean up and check what was typed.

    if (!$errors && email_taken($conn, $adopter["email"])) {   // No two adopters can share an email (Business rule #7).
        $errors[] = "Another adopter already uses that email (Business rule #7).";
    }

    if (!$errors) {                                // Only save when there are no problems.
        $new_id = next_adopter_id($conn);          // Worked out again right before saving, in case someone else just added one.
        try {
            $stmt = $conn->prepare(
                "INSERT INTO adopters (adopter_id, first_name, last_name, phone, email, address, date_of_birth)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"     // date_joined is left out, so MySQL uses today.
            );
            $stmt->bind_param("issssss",           // i = whole number, s = string.
                $new_id, $adopter["first_name"], $adopter["last_name"], $adopter["phone"], $adopter["email"], $adopter["address"], $adopter["date_of_birth"]);
            $stmt->execute();                      // Save the new adopter.

            header("Location: index.php?done=added");   // Go back to the list, which shows "The adopter was added."
            exit;                                  // Stop here, so nothing else runs after the redirect.
        } catch (mysqli_sql_exception $e) {
            // 1062 = a repeat of something that must be unique. It can only happen if someone else saved at the same moment.
            if ($e->getCode() !== 1062) {
                die("The adopter couldn't be added: " . $e->getMessage());   // Any other error is a real problem, so show it.
            }
            $errors[] = str_contains($e->getMessage(), "uq_adopters_email")   // MySQL names the rule that stopped it.
                ? "Another adopter already uses that email (Business rule #7)."
                : "Someone else added an adopter at the same moment. Please click Add adopter again.";   // Two people got the same new ID.
        }
    }
}

// 5. SHOW THE PAGE
$next_id    = next_adopter_id($conn);              // For the note under the form.
$page_title = "Add an adopter";                    // Tab title.
$active     = "adopters";                          // Highlight Adopters in the menu.
require_once __DIR__ . "/../includes/header.php";  // Top of the page.
?>
<h1>Add an adopter</h1>

<form method="post">                                <!-- POST sends the form to this same page. -->
    <?php require __DIR__ . "/adopter-form.php";   // The error box and adopter fields, shared with edit.php. ?>

    <p class="note">This adopter will get ID <?= $next_id ?> (the largest ID + 1), and today's date is saved as the date joined (Business rule #12).</p>

    <div class="form-buttons">
        <button class="button" type="submit">Add adopter</button>
        <a href="index.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . "/../includes/footer.php";   // Bottom of the page. ?>
