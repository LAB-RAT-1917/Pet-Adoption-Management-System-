<?php
// add.php
// Purpose: The form to add a new pet (Pets requirement #1).
// New pets always start as Available, and MySQL fills in the date added (Business rules #12 and #19).

// 1. CONNECT AND LOAD THE CHECKS
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/check-pet.php";           // check_pet_form(), shared with edit.php.

// 2. START WITH AN EMPTY FORM
$pet = ["name" => "", "species" => "", "breed" => "", "birth_date" => "", "estimated_age" => "", "adoption_fee" => "", "vaccinated" => ""];
$errors = [];
$arrival_date = date("Y-m-d");                     // A new pet arrives today. Its age today sets the price (Business rule #30).

// 3. SAVE THE PET WHEN THE FORM IS SENT
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$pet, $errors] = check_pet_form($_POST, $arrival_date);

    if (!$errors) {
        $stmt = $conn->prepare(
            "INSERT INTO pets (name, species, breed, birth_date, estimated_age, adoption_fee, vaccinated)
             VALUES (?, ?, ?, ?, ?, ?, ?)"         // status and date_added are left out, so MySQL uses 'Available' and today.
        );
        $stmt->bind_param("ssssidi",               // s = string, i = whole number, d = decimal number.
            $pet["name"], $pet["species"], $pet["breed"], $pet["birth_date"], $pet["estimated_age"], $pet["adoption_fee"], $pet["vaccinated"]);
        $stmt->execute();

        header("Location: index.php?done=added");  // Back to the list, which shows "The pet was added."
        exit;
    }
}

// 4. SHOW THE PAGE
$page_title = "Add a pet";
$active     = "pets";
require_once __DIR__ . "/../includes/header.php";
?>
<h1>Add a pet</h1>

<form method="post">
    <?php require __DIR__ . "/pet-form.php";   // The error box and pet fields, shared with edit.php. ?>

    <p class="note">New pets start as Available, and today's date is saved as the date added.</p>

    <div class="form-buttons">
        <button class="button" type="submit">Add pet</button>
        <a href="index.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
