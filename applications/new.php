<?php
// applications/new.php
// Purpose: Staff enter a new adoption application for one adopter and one pet.
// PHP checks Business rules #13, #14 and #15 before saving:
//   Breaks a rule  -> saved as Rejected, with the matching reason (Business rule #25).
//   Passes them all -> saved as Pending, and the pet becomes Pending.
// Saving the application and changing the pet run as one transaction (Business rule #16).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Shared connection ($conn).

// reason_id of each automatic rejection in rejection_reasons (schema.sql adds them in this order).
const REASON_UNDER_18        = 1;   // Business rule #14
const REASON_NOT_VACCINATED  = 2;   // Business rule #13
const REASON_PET_ADOPTED     = 3;   // Business rule #13
const REASON_PET_PENDING     = 4;   // Business rule #13
const REASON_PET_RETURNED    = 5;   // Business rules #13 and #18
const REASON_ADOPTER_PENDING = 6;   // Business rule #15

// Counts rows for one query with one whole-number value, like "pending applications for pet 12".
function count_rows(mysqli $conn, string $sql, int $id): int
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_row()[0];
}

// Returns the reason_id of the first rule this application breaks, or null if it breaks none.
function broken_rule(mysqli $conn, array $adopter, array $pet, int $adopter_id, int $pet_id): ?int
{
    // Business rule #14: the adopter must be 18 or older.
    $age = (new DateTime($adopter["date_of_birth"]))->diff(new DateTime("today"))->y;
    if ($age < 18) return REASON_UNDER_18;

    // Business rule #13: the pet must be in the store, free, and vaccinated.
    if ($pet["status"] === "Adopted")  return REASON_PET_ADOPTED;
    if ($pet["status"] === "Returned") return REASON_PET_RETURNED;
    if (!$pet["vaccinated"])           return REASON_NOT_VACCINATED;
    if ($pet["status"] === "Pending" ||
        count_rows($conn, "SELECT COUNT(*) FROM applications WHERE pet_id = ? AND status = 'Pending'", $pet_id) > 0) {
        return REASON_PET_PENDING;
    }

    // Business rule #15: an adopter can have only one Pending application at a time.
    if (count_rows($conn, "SELECT COUNT(*) FROM applications WHERE adopter_id = ? AND status = 'Pending'", $adopter_id) > 0) {
        return REASON_ADOPTER_PENDING;
    }

    return null;   // No rule broken.
}

// 2. HANDLE THE FORM
$errors      = [];
$adopter_id  = $_POST["adopter_id"]  ?? "";   // Kept so the dropdowns stay filled in after an error.
$pet_id      = $_POST["pet_id"]      ?? "";
$employee_id = $_POST["employee_id"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Server-side check: each value must be a whole number (adopter IDs can be negative).
    $adopter_id  = filter_var($adopter_id,  FILTER_VALIDATE_INT);
    $pet_id      = filter_var($pet_id,      FILTER_VALIDATE_INT);
    $employee_id = filter_var($employee_id, FILTER_VALIDATE_INT);
    if ($adopter_id === false)  $errors[] = "Please choose an adopter.";
    if ($pet_id === false)      $errors[] = "Please choose a pet.";
    if ($employee_id === false) $errors[] = "Please choose the employee handling this application.";

    if (!$errors) {
        $in_transaction = false;
        try {
            $conn->begin_transaction();
            $in_transaction = true;

            // Look up the adopter.
            $stmt = $conn->prepare("SELECT date_of_birth FROM adopters WHERE adopter_id = ?");
            $stmt->bind_param("i", $adopter_id);
            $stmt->execute();
            $adopter = $stmt->get_result()->fetch_assoc();

            // Look up the pet. FOR UPDATE locks it until we finish, so two staff can't book it at once.
            $stmt = $conn->prepare("SELECT status, vaccinated FROM pets WHERE pet_id = ? FOR UPDATE");
            $stmt->bind_param("i", $pet_id);
            $stmt->execute();
            $pet = $stmt->get_result()->fetch_assoc();

            // Look up the employee.
            $stmt = $conn->prepare("SELECT department FROM employees WHERE employee_id = ?");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $employee = $stmt->get_result()->fetch_assoc();

            if (!$adopter) $errors[] = "That adopter does not exist.";
            if (!$pet)     $errors[] = "That pet does not exist.";
            if (!$employee) {
                $errors[] = "That employee does not exist.";
            } elseif ($employee["department"] !== "Adoption") {
                $errors[] = "Only Adoption staff can handle applications (Business rule #22).";
            }

            if ($errors) {
                $conn->rollback();
                $in_transaction = false;
            } else {
                $reason_id = broken_rule($conn, $adopter, $pet, $adopter_id, $pet_id);
                $status    = $reason_id === null ? "Pending" : "Rejected";

                // Save the application. A NULL reason_id is saved as NULL.
                $stmt = $conn->prepare(
                    "INSERT INTO applications (adopter_id, pet_id, employee_id, status, rejection_reason_id)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("iiisi", $adopter_id, $pet_id, $employee_id, $status, $reason_id);
                $stmt->execute();
                $new_id = $conn->insert_id;

                if ($status === "Pending") {
                    // The pet is now on hold for this adopter.
                    $stmt = $conn->prepare("UPDATE pets SET status = 'Pending' WHERE pet_id = ?");
                    $stmt->bind_param("i", $pet_id);
                    $stmt->execute();
                }

                $conn->commit();   // The application and the pet are saved together.
                $in_transaction = false;

                // Send staff to the list the application landed in.
                if ($status === "Pending") {
                    header("Location: index.php?type=success&msg=" . urlencode("Application #$new_id was saved as Pending."));
                } else {
                    header("Location: rejected.php?type=error&msg=" . urlencode("Application #$new_id breaks a business rule, so it was saved as Rejected. See the reason below."));
                }
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($in_transaction) {
                $conn->rollback();   // Nothing is saved if any step fails.
            }
            $errors[] = "The application could not be saved. Please try again.";
        }
    }
}

// 3. GET THE DROPDOWN LISTS
$adopters = $conn->query(
    "SELECT adopter_id, first_name, last_name, phone FROM adopters ORDER BY last_name, first_name"
)->fetch_all(MYSQLI_ASSOC);

// Every pet is listed with its status, so staff can see why an application gets rejected.
$pets = $conn->query(
    "SELECT pet_id, name, species, status, vaccinated FROM pets ORDER BY name"
)->fetch_all(MYSQLI_ASSOC);

// Only Adoption staff appear in the Handled by list (Business rule #22).
$employees = $conn->query(
    "SELECT employee_id, first_name, last_name FROM employees WHERE department = 'Adoption' ORDER BY last_name, first_name"
)->fetch_all(MYSQLI_ASSOC);

// Marks the option that was chosen before, so the form stays filled in after an error.
function selected($value, $chosen): string
{
    return (string) $value === (string) $chosen ? " selected" : "";
}

// 4. SHOW THE PAGE
$page_title = "New Application";
$active     = "applications";
$tab        = "new";
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/nav.php";
?>
<h1>New Application</h1>
<p>Choose the adopter, the pet and the Adoption employee going over the application.
   The system checks the business rules when you submit.</p>

<?php if ($errors): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="new.php" id="application-form" novalidate>
    <label for="adopter_id">Adopter</label>
    <select name="adopter_id" id="adopter_id" required>
        <option value="">-- Choose an adopter --</option>
        <?php foreach ($adopters as $a): ?>
            <option value="<?= (int) $a["adopter_id"] ?>"<?= selected($a["adopter_id"], $adopter_id) ?>>
                <?= htmlspecialchars($a["last_name"] . ", " . $a["first_name"] . " – " . $a["phone"]) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="pet_id">Pet</label>
    <select name="pet_id" id="pet_id" required>
        <option value="">-- Choose a pet --</option>
        <?php foreach ($pets as $p): ?>
            <option value="<?= (int) $p["pet_id"] ?>"<?= selected($p["pet_id"], $pet_id) ?>>
                <?= htmlspecialchars($p["name"] . " – " . $p["species"] . " – " . $p["status"] . ($p["vaccinated"] ? "" : " – not vaccinated")) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="employee_id">Handled by</label>
    <select name="employee_id" id="employee_id" required>
        <option value="">-- Choose an employee --</option>
        <?php foreach ($employees as $emp): ?>
            <option value="<?= (int) $emp["employee_id"] ?>"<?= selected($emp["employee_id"], $employee_id) ?>>
                <?= htmlspecialchars($emp["first_name"] . " " . $emp["last_name"] . " (#" . $emp["employee_id"] . ")") ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p id="form-error" class="message error" hidden></p>
    <p><button type="submit" class="button">Submit Application</button></p>
</form>

<script>
// Client-side check: all three dropdowns must be chosen. PHP checks again on the server.
document.getElementById("application-form").addEventListener("submit", function (event) {
    var missing = ["adopter_id", "pet_id", "employee_id"].filter(function (id) {
        return document.getElementById(id).value === "";
    });
    var box = document.getElementById("form-error");
    if (missing.length > 0) {
        event.preventDefault();   // Don't send the form.
        box.textContent = "Please choose an adopter, a pet and an employee.";
        box.hidden = false;
    }
});
</script>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
