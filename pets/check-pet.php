<?php
// check-pet.php
// Project: Pet Adoption Management System (CIS 344 Group Project).
// Purpose: The fee rules and the checks for the pet form. add.php and edit.php both use it, so they work the same way.
// This file has no closing PHP tag on purpose, like db.php.

// 1. SET PRICES (Business rules #29 to #32)
// Dogs, cats, rabbits and birds have a set price for their age on the day they arrive (Business rules #29 and #30).
// If their birth date is unknown, staff type an estimated age, and the price uses that (Business rule #31).
// Other animals have no set price. Staff type their fee, from $10 to $75 (Business rule #32).
// These numbers must match chk_pets_fee_by_age in schema.sql, so PHP and MySQL agree.
function pet_set_prices(): array
{
    return [                                    // species => [under 1 year, 1 to 6 years, 7 and up]
        "Dog"    => [250, 175, 100],
        "Cat"    => [150, 100, 50],
        "Rabbit" => [75, 50, 25],
        "Bird"   => [100, 60, 30],
    ];
}

// pet_age_on_arrival() gives a pet's age in full years on the day it arrived (Business rule #30).
// It uses the birth date, or the estimate when there's no birth date (Business rule #31). null means the age is unknown.
function pet_age_on_arrival(?string $birth_date, ?int $estimated_age, string $arrival_date): ?int
{
    if ($birth_date === null || $birth_date === "") {
        return $estimated_age;                                      // No birth date, so use the estimate (null if there's none).
    }
    if ($birth_date > $arrival_date) {
        return 0;                                                   // A birth date after arrival counts as under 1 year.
    }
    return (new DateTime($birth_date))->diff(new DateTime($arrival_date))->y;   // Full years old on the day it arrived.
}

// pet_set_fee() gives the set price for a species and age, or null for other animals.
function pet_set_fee(string $species, int $age): ?float
{
    $prices = pet_set_prices();
    if (!isset($prices[$species])) {
        return null;                                                // Other animals: staff type the fee (Business rule #32).
    }
    $group = $age < 1 ? 0 : ($age <= 6 ? 1 : 2);                    // 0 = under 1 year, 1 = 1 to 6 years, 2 = 7 and up.
    return $prices[$species][$group];                               // The price from that column of the list (Business rule #29).
}

// 2. CHECK THE FORM
// check_pet_form() takes what staff typed and the day the pet arrived (today for a new pet), and gives back two things:
//   $pet    = the cleaned-up values, ready to save.
//   $errors = a list of problems to show. An empty list means the form is OK.
function check_pet_form(array $form, string $arrival_date): array
{
    $species_list = ["Dog", "Cat", "Rabbit", "Bird", "Other"];     // Same list as the species column (Business rule #10).
    $errors = [];                                                    // Problems found so far.

    // Clean up the values. Without this, extra spaces or a missing field could sneak into the database.
    $pet = [
        "name"          => trim($form["name"] ?? ""),                // trim removes spaces at the start and end.
        "species"       => $form["species"] ?? "",
        "breed"         => trim($form["breed"] ?? ""),
        "birth_date"    => $form["birth_date"] ?? "",
        "estimated_age" => trim($form["estimated_age"] ?? ""),       // Only sent when the birth date is blank (Business rule #31).
        "adoption_fee"  => trim($form["adoption_fee"] ?? ""),
        "vaccinated"    => (string) ($form["vaccinated"] ?? ""),     // "1" for Yes, "0" for No, "" if nothing was picked.
    ];
    $set_price = isset(pet_set_prices()[$pet["species"]]);           // True for dogs, cats, rabbits and birds.

    // Check each value. Without these checks, bad data could be saved, or MySQL would stop the page with an error.
    if ($pet["name"] === "") {
        $errors[] = "Name is required (Business rule #5).";
    } elseif (mb_strlen($pet["name"]) > 50) {                        // mb_strlen counts letters like é as one.
        $errors[] = "Name can be 50 characters at most.";
    }

    if (!in_array($pet["species"], $species_list, true)) {
        $errors[] = "Pick a species from the list (Business rule #10).";
    }

    if (mb_strlen($pet["breed"]) > 50) {
        $errors[] = "Breed can be 50 characters at most.";
    }

    // The age: a birth date, or an estimate when the birth date is unknown (Business rules #5 and #31).
    // Without this, a dog, cat, rabbit or bird could be saved with no age, and its price couldn't be worked out.
    $age_ok = true;                                                  // Stays true if the age details are fine.
    if ($pet["birth_date"] !== "") {                                 // Birth date is optional (Business rule #5).
        $date = DateTime::createFromFormat("Y-m-d", $pet["birth_date"]);
        if (!$date || $date->format("Y-m-d") !== $pet["birth_date"]) {    // Catches dates like 2025-02-30.
            $errors[] = "Birth date isn't a real date.";
            $age_ok = false;
        } elseif ($pet["birth_date"] > $arrival_date) {
            $errors[] = "Birth date can't be after the day the pet arrived.";
            $age_ok = false;
        }
        $pet["estimated_age"] = "";                                  // A birth date wins, so any estimate is dropped (Business rule #31).
    } elseif ($pet["estimated_age"] !== "") {                        // No birth date, but staff typed an estimate.
        if (!preg_match('/^\d{1,2}$/', $pet["estimated_age"]) || $pet["estimated_age"] > 50) {   // Whole years only, 0 to 50.
            $errors[] = "Estimated age must be a whole number from 0 to 50 (Business rule #31).";
            $age_ok = false;
        }
    } elseif ($set_price) {                                          // Dogs, cats, rabbits and birds need one or the other.
        $errors[] = "Type the birth date, or an estimated age if the birth date is unknown (Business rule #31).";
        $age_ok = false;
    }

    if ($pet["vaccinated"] !== "1" && $pet["vaccinated"] !== "0") {  // Only Yes or No counts as an answer.
        $errors[] = "Answer Yes or No for \"Is the pet vaccinated?\" (Business rule #5).";
    }

    // The fee. Without this, the fee wouldn't follow the store's prices.
    if ($pet["species"] === "Other") {                               // Other animals: staff type the fee (Business rule #32).
        if (!preg_match('/^\d{1,4}(\.\d{1,2})?$/', $pet["adoption_fee"])) {   // Numbers only, with up to 2 after the dot.
            $errors[] = "Adoption fee must be numbers only, like 40 or 39.50 (Business rule #32).";
        } elseif ($pet["adoption_fee"] < 10 || $pet["adoption_fee"] > 75) {
            $errors[] = 'Fees for other animals must be between $10 and $75 (Business rule #32).';
        }
    } elseif ($set_price && $age_ok) {                               // Dogs, cats, rabbits and birds: the set price (Business rule #29).
        $estimate = $pet["estimated_age"] === "" ? null : (int) $pet["estimated_age"];   // The estimate as a whole number, or null.
        $age = pet_age_on_arrival($pet["birth_date"], $estimate, $arrival_date);         // Age on the day it arrived (Business rule #30).
        $pet["adoption_fee"] = pet_set_fee($pet["species"], $age);   // The set price. Anything typed in the fee box is ignored.
    }

    // Blank optional fields become NULL. Without this, a blank breed would be saved as an empty word instead of "unknown".
    $pet["breed"]         = $pet["breed"] === "" ? null : $pet["breed"];
    $pet["birth_date"]    = $pet["birth_date"] === "" ? null : $pet["birth_date"];
    $pet["estimated_age"] = $pet["estimated_age"] === "" ? null : $pet["estimated_age"];   // No estimate is saved as NULL.

    return [$pet, $errors];                                          // Send both back to the page.
}
