<?php
// edit.php
// Project: Pet Adoption Management System (CIS 344 Group Project).
// Purpose: The form to edit a pet's details (Pets requirement #2).
// By hand, staff can only change Adopted to Returned, and Returned to Available (Business rule #19).

// 1. CONNECT AND LOAD THE CHECKS
require_once __DIR__ . "/../includes/db.php";      // The shared connection ($conn).
require_once __DIR__ . "/check-pet.php";           // check_pet_form(), shared with add.php.

// 2. FIND THE PET
// Without this, the page wouldn't know which pet to edit. The ID comes from the address, like edit.php?id=18.
$id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);   // A whole number, or false if it isn't one.
$stmt = $conn->prepare(
    "SELECT pet_id, name, species, breed, birth_date, estimated_age, status, date_added, adoption_fee, vaccinated
     FROM pets WHERE pet_id = ?"
);
$stmt->bind_param("i", $id);                       // i = whole number.
$stmt->execute();
$saved = $stmt->get_result()->fetch_assoc();       // The pet as it is now, or null if there's no pet with that ID.

// 3. WHICH STATUS CHANGES ARE ALLOWED
// Without this list, staff could make a pet Pending or Adopted by hand and skip the application (Business rule #19).
$status_choices = [                                // Current status => the choices staff get.
    "Adopted"   => ["Adopted"  => "Adopted (no change)",
                    "Returned" => "Returned (the adopter brought the pet back)"],
    "Returned"  => ["Returned"  => "Returned (no change)",
                    "Available" => "Available (cared for and ready for a new home)"],
    "Available" => [],                             // Changes only through applications.
    "Pending"   => [],                             // Changes only through applications.
];
$choices = $saved ? $status_choices[$saved["status"]] : [];

// 4. START WITH THE SAVED VALUES
$errors = [];                                      // No problems until the form is sent.
if ($saved) {
    $pet = $saved;                                 // Fill the form with what's saved.
    $pet["vaccinated"] = (string) $saved["vaccinated"];   // "1" or "0", the same way the form sends it.
    $status = $saved["status"];
    $arrival_date = $saved["date_added"];          // The price uses the pet's age on the day it arrived (Business rule #30).
}

// 5. SAVE THE CHANGES WHEN THE FORM IS SENT
// Without this, clicking "Save changes" would just reload the page.
if ($saved && $_SERVER["REQUEST_METHOD"] === "POST") {
    [$pet, $errors] = check_pet_form($_POST, $arrival_date);   // Same checks as the Add page.
    $status = $_POST["status"] ?? $saved["status"];   // No status box on the form means the status stays the same.

    if ($status !== $saved["status"] && !isset($choices[$status])) {   // Checked here too, in case someone edits the form.
        $errors[] = "That status change isn't allowed. By hand, staff can only change Adopted to Returned, and Returned to Available (Business rule #19).";
    }

    if (!$errors) {                                // Only save when there are no problems.
        $stmt = $conn->prepare(
            "UPDATE pets
             SET name = ?, species = ?, breed = ?, birth_date = ?, estimated_age = ?, adoption_fee = ?, vaccinated = ?, status = ?
             WHERE pet_id = ?"                     // date_added never changes (Business rule #12).
        );
        $stmt->bind_param("ssssidisi",             // s = string, i = whole number, d = decimal number.
            $pet["name"], $pet["species"], $pet["breed"], $pet["birth_date"], $pet["estimated_age"], $pet["adoption_fee"], $pet["vaccinated"], $status, $id);
        $stmt->execute();                          // Save the changes.

        header("Location: index.php?done=updated");   // Back to the list, which shows "The pet was updated."
        exit;
    }
}

// 6. SHOW THE PAGE
$page_title = $saved ? "Edit " . $saved["name"] : "Pet not found";   // Tab title.
$active     = "pets";                              // Highlight Pets in the menu.
require_once __DIR__ . "/../includes/header.php";  // Top of the page.
?>
<?php if (!$saved): ?>                              <!-- Wrong or missing ID in the address. -->
    <h1>Pet not found</h1>
    <p class="message error">There's no pet with that ID.</p>
    <p><a href="index.php">Back to the pet list</a></p>
<?php else: ?>
    <h1>Edit <?= htmlspecialchars($saved["name"]) ?></h1>
    <p class="note">Pet ID <?= $saved["pet_id"] ?> &middot; Added <?= date("M j, Y", strtotime($saved["date_added"])) ?></p>

    <form method="post">                            <!-- Sends the form to this same address, so the ID stays. -->
        <?php require __DIR__ . "/pet-form.php";   // The error box and pet fields, shared with add.php. ?>

        <?php if ($choices): ?>                     <!-- Only Adopted and Returned pets get a status box. -->
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php foreach ($choices as $value => $text): ?>
                    <option value="<?= $value ?>"<?= $value === $status ? " selected" : "" ?>><?= $text ?></option>
                <?php endforeach; ?>
            </select>
            <p class="note">By hand, staff can only change Adopted to Returned, and Returned to Available (Business rule #19).</p>
        <?php else: ?>
            <p><strong>Status:</strong>
               <span class="status <?= $saved["status"] ?>"><?= $saved["status"] ?></span>
               This only changes through applications (Business rule #19).</p>
        <?php endif; ?>

        <div class="form-buttons">
            <button class="button" type="submit">Save changes</button>
            <a href="index.php">Cancel</a>
        </div>
    </form>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php";   // Bottom of the page. ?>
