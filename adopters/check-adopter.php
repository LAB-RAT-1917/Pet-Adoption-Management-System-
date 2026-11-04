<?php
// check-adopter.php
// Purpose: The checks for the adopter form. add.php and edit.php both use it, so they check adopters the same way.
// This file has no closing PHP tag on purpose, like db.php.

// 1. CHECK THE FORM
// check_adopter_form() takes what staff typed and gives back two things:
//   $adopter = the cleaned-up values, ready to save.
//   $errors  = a list of problems to show. An empty list means the form is OK.
function check_adopter_form(array $form): array
{
    $errors = [];

    $adopter = [];
    foreach (["first_name", "last_name", "phone", "email", "address", "date_of_birth"] as $field) {
        $value = $form[$field] ?? "";
        $adopter[$field] = is_string($value) ? trim($value) : "";    // trim removes spaces at the start and end. A list counts as blank.
    }

    // Every field is required (Business rule #6). Without these checks, MySQL would stop the page with an error.
    if ($adopter["first_name"] === "") {
        $errors[] = "First name is required (Business rule #6).";
    } elseif (mb_strlen($adopter["first_name"]) > 50) {             // mb_strlen counts letters like é as one.
        $errors[] = "First name can be 50 characters at most.";
    }

    if ($adopter["last_name"] === "") {
        $errors[] = "Last name is required (Business rule #6).";
    } elseif (mb_strlen($adopter["last_name"]) > 50) {
        $errors[] = "Last name can be 50 characters at most.";
    }

    if ($adopter["phone"] === "") {
        $errors[] = "Phone is required (Business rule #6).";
    } elseif (!preg_match('/^[0-9 ()+.\-]{7,20}$/', $adopter["phone"])      // Only numbers, spaces, ( ) + . and -.
              || preg_match_all('/[0-9]/', $adopter["phone"]) < 7) {       // And at least 7 numbers.
        $errors[] = "Phone needs at least 7 numbers, and can only use numbers, spaces and ( ) - + . like (718) 555-0123.";
    }

    if ($adopter["email"] === "") {
        $errors[] = "Email is required (Business rule #6).";
    } elseif (mb_strlen($adopter["email"]) > 100 || !filter_var($adopter["email"], FILTER_VALIDATE_EMAIL)) {   // PHP's own email check.
        $errors[] = "Email must be a real email address, like name@example.com.";
    }

    if ($adopter["address"] === "") {
        $errors[] = "Address is required (Business rule #6).";
    } elseif (mb_strlen($adopter["address"]) > 150) {
        $errors[] = "Address can be 150 characters at most.";
    }

    if ($adopter["date_of_birth"] === "") {
        $errors[] = "Date of birth is required (Business rule #6).";
    } else {
        $date = DateTime::createFromFormat("Y-m-d", $adopter["date_of_birth"]);
        if (!$date || $date->format("Y-m-d") !== $adopter["date_of_birth"]) {    // Catches dates like 2025-02-30.
            $errors[] = "Date of birth isn't a real date.";
        } elseif ($adopter["date_of_birth"] > date("Y-m-d")) {
            $errors[] = "Date of birth can't be in the future.";
        } elseif ($adopter["date_of_birth"] < "1900-01-01") {                    // Catches typos like year 0198.
            $errors[] = "Date of birth must be in 1900 or later.";
        }
    }

    return [$adopter, $errors];
}

// 2. CHECK FOR A REPEATED EMAIL (Business rule #7)
// email_taken() says whether another adopter already uses this email.
// $skip_id is the adopter being edited, so their own email doesn't count. 0 means "skip no one", since no adopter has ID 0.
// Without this, staff would get a MySQL error (1062) instead of a clear message.
function email_taken(mysqli $conn, string $email, int $skip_id = 0): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM adopters WHERE email = ? AND adopter_id <> ?");
    $stmt->bind_param("si", $email, $skip_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0] > 0;
}
