<?php
// add.php
// Project: Pet Adoption Management System (CIS 344 Group Project).
// Purpose: The form to add a new pet (Pets requirement #1).
// New pets always start as Available, and MySQL fills in the date added (Business rules #12 and #19).

// 1. CONNECT AND LOAD THE CHECKS
require_once __DIR__ . "/../includes/db.php";      // The shared connection ($conn).
require_once __DIR__ . "/check-pet.php";           // check_pet_form(), shared with edit.php.

// 2. START WITH AN EMPTY FORM
$pet = ["name" => "", "species" => "", "breed" => "", "birth_date" => "", "estimated_age" => "", "adoption_fee" => "", "vaccinated" => ""];   // "" = not filled in yet.
$errors = [];                                      // No problems until the form is sent.
$arrival_date = date("Y-m-d");                     // A new pet arrives today. Its age today sets the price (Business rule #30).

// 3. SAVE THE PET WHEN THE FORM IS SENT
// Without this, clicking "Add pet" would just reload the page and save nothing.
if ($_SERVER["REQUEST_METHOD"] === "POST") {       // POST means the form was sent.
    [$pet, $errors] = check_pet_form($_POST, $arrival_date);   // Clean up and check what was typed, and set the price.

    if (!$errors) {                                // Only save when there are no problems.
        $stmt = $conn->prepare(
            "INSERT INTO pets (name, species, breed, birth_date, estimated_age, adoption_fee, vaccinated)
             VALUES (?, ?, ?, ?, ?, ?, ?)"         // status and date_added are left out, so MySQL uses 'Available' and today.
        );
        $stmt->bind_param("ssssidi",               // s = string, i = whole number, d = decimal number.
            $pet["name"], $pet["species"], $pet["breed"], $pet["birth_date"], $pet["estimated_age"], $pet["adoption_fee"], $pet["vaccinated"]);
        $stmt->execute();                          // Save the new pet.

        header("Location: index.php?done=added");  // Go back to the list, which shows "The pet was added."
        exit;                                      // Stop here, so nothing else runs after the redirect.
    }
}

// 4. SHOW THE PAGE
$page_title = "Add a pet";                         // Tab title.
$active     = "pets";                              // Highlight Pets in the menu.
require_once __DIR__ . "/../includes/header.php";  // Top of the page.
?>
<h1>Add a pet</h1>

<form method="post">                                <!-- POST sends the form to this same page. -->
    <?php require __DIR__ . "/pet-form.php";   // The error box and pet fields, shared with edit.php. ?>

    <p class="note">New pets start as Available, and today's date is saved as the date added.</p>

    <div class="form-buttons">
        <button class="button" type="submit">Add pet</button>
        <a href="index.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . "/../includes/footer.php";   // Bottom of the page. ?>
