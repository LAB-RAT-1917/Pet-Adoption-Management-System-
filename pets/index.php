<?php
// pets/index.php
// Purpose: Lists every pet, with a filter box on every column. Links to add, edit and delete a pet.

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";

// 2. THE PET LIST
// One row per pet, plus three worked-out columns. The filter lists and the table all use these same rows.
// age: from the birth date, or else the estimate plus the years since it arrived (Business rule #31). COALESCE takes the first one that isn't NULL.
// age_is_estimate: 1 when the age came from an estimate, so the table can say "About".
// adopted_by: the newest Approved application's adopter. It stays blank for pets that aren't adopted,
// because adoption is stored on the application, not the pet.
$pet_list = "SELECT p.pet_id, p.name, p.species, p.breed, p.status, p.adoption_fee, p.vaccinated,
                    COALESCE(TIMESTAMPDIFF(YEAR, p.birth_date, CURDATE()),
                             p.estimated_age + TIMESTAMPDIFF(YEAR, p.date_added, CURDATE())) AS age,
                    p.birth_date IS NULL AND p.estimated_age IS NOT NULL AS age_is_estimate,
                    (SELECT CONCAT(ad.first_name, ' ', ad.last_name)
                     FROM applications a
                     INNER JOIN adopters ad ON ad.adopter_id = a.adopter_id
                     WHERE a.pet_id = p.pet_id AND a.status = 'Approved' AND p.status IN ('Adopted', 'Returned')
                     ORDER BY a.application_date DESC
                     LIMIT 1) AS adopted_by
             FROM pets p";

// 3. THE CHOICES FOR EACH FILTER
// Every filter is checked against its own list. These queries have no typed input, so a plain query is safe.
$species_list = ["Dog", "Cat", "Rabbit", "Bird", "Other"];                  // Business rule #10
$status_list  = ["Available", "Pending", "Adopted", "Returned"];            // Business rule #8
$age_groups   = [                                                           // Age group => its test. Only these set tests can run.
    "Under 1 year" => "age < 1",
    "1 to 6 years" => "age BETWEEN 1 AND 6",
    "7 and up"     => "age >= 7",
    "Unknown"      => "age IS NULL",
];
$breeds   = array_column($conn->query("SELECT DISTINCT breed FROM pets WHERE breed IS NOT NULL ORDER BY breed")->fetch_all(), 0);
$fees     = array_column($conn->query("SELECT DISTINCT adoption_fee FROM pets ORDER BY adoption_fee")->fetch_all(), 0);
$adopters = array_column($conn->query("SELECT DISTINCT adopted_by FROM ($pet_list) AS pet_list
                                       WHERE adopted_by IS NOT NULL ORDER BY adopted_by")->fetch_all(), 0);

// 4. READ THE FILTERS
// Each filter comes from the address, like ?species=Dog&status=Pending. "" means "show all".
// Anything that isn't on its list is thrown out, so a changed address can't break the page (Pets requirement #5).
function get_filter(string $key): string                                    // A list or anything that isn't text counts as "".
{
    $value = $_GET[$key] ?? "";
    return is_string($value) ? trim($value) : "";
}
$f = [];
$f["id"]         = filter_var(get_filter("id"), FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: "";
$f["name"]       = mb_substr(get_filter("name"), 0, 50);
$f["species"]    = in_array(get_filter("species"), $species_list, true) ? get_filter("species") : "";
$f["breed"]      = in_array(get_filter("breed"), [...$breeds, "Unknown"], true) ? get_filter("breed") : "";   // "Unknown" = no breed.
$f["age"]        = isset($age_groups[get_filter("age")]) ? get_filter("age") : "";
$f["status"]     = in_array(get_filter("status"), $status_list, true) ? get_filter("status") : "";
$f["fee"]        = in_array(get_filter("fee"), $fees, true) ? get_filter("fee") : "";
$f["vaccinated"] = in_array(get_filter("vaccinated"), ["Yes", "No"], true) ? get_filter("vaccinated") : "";
$f["adopted_by"] = in_array(get_filter("adopted_by"), $adopters, true) ? get_filter("adopted_by") : "";

// 5. GET THE PETS THAT MATCH
// Each filter that's set adds one test, and each ? gets its value from $params, in order.
$where  = [];
$params = [];
if ($f["id"] !== "")             { $where[] = "pet_id = ?";                    $params[] = $f["id"]; }
if ($f["name"] !== "")           { $where[] = "name LIKE CONCAT('%', ?, '%')"; $params[] = $f["name"]; }
if ($f["species"] !== "")        { $where[] = "species = ?";                   $params[] = $f["species"]; }
if ($f["breed"] === "Unknown")   { $where[] = "breed IS NULL"; }
elseif ($f["breed"] !== "")      { $where[] = "breed = ?";                     $params[] = $f["breed"]; }
if ($f["age"] !== "")            { $where[] = $age_groups[$f["age"]]; }
if ($f["status"] !== "")         { $where[] = "status = ?";                    $params[] = $f["status"]; }
if ($f["fee"] !== "")            { $where[] = "adoption_fee = ?";              $params[] = $f["fee"]; }
if ($f["vaccinated"] !== "")     { $where[] = "vaccinated = ?";                $params[] = $f["vaccinated"] === "Yes" ? "1" : "0"; }   // Saved as 1 = Yes, 0 = No.
if ($f["adopted_by"] !== "")     { $where[] = "adopted_by = ?";                $params[] = $f["adopted_by"]; }

$sql = "SELECT * FROM ($pet_list) AS pet_list"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")                // AND means a pet must pass every filter.
     . " ORDER BY pet_id";
$stmt = $conn->prepare($sql);                                               // Prepared statement, so typed filters can't change the SQL.
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);         // One "s" (text) per value. "..." gives each value its own ?.
}
$stmt->execute();
$pets = $stmt->get_result();
$total = $conn->query("SELECT COUNT(*) FROM pets")->fetch_row()[0];         // For "Showing 10 of 38 pets."

// options() prints the choices for one filter, and keeps the current choice picked after the page reloads.
// $prefix goes in front of each choice on the screen, like "$" for the fees.
function options(array $values, string $current, string $prefix = ""): void
{
    echo '<option value="">All</option>';
    foreach ($values as $value) {
        $selected = $value === $current ? " selected" : "";
        echo '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($prefix . $value) . '</option>';
    }
}

// 6. MESSAGE FROM THE ADD, EDIT OR DELETE PAGE
// Only these set messages can show.
$messages = [
    "added"   => ["success", "The pet was added."],
    "updated" => ["success", "The pet was updated."],
    "deleted" => ["success", "The pet was deleted."],
    "in-use"  => ["error",   "That pet has applications, so it can't be deleted (Business rules #17 and #28)."],
    "missing" => ["error",   "That pet wasn't found. It may have been deleted already."],
];
$done = $messages[get_filter("done")] ?? null;

// 7. SHOW THE PAGE
$page_title = "Pets";
$active     = "pets";
require_once __DIR__ . "/../includes/header.php";
?>
<h1>Pets</h1>

<?php if ($done): ?>
    <p class="message <?= $done[0] ?>"><?= $done[1] ?></p>
<?php endif; ?>

<div class="list-top">
    <a class="button" href="add.php">Add a pet</a>
    <span><?= $where ? "Showing " . $pets->num_rows . " of " . $total . " pets." : "Showing all " . $total . " pets." ?></span>
</div>

<?php // The filter boxes sit inside the table, and form="filters" ties each one to this form. ?>
<?php // That way each row's Delete form stays separate, because a form can't go inside another form. ?>
<form method="get" id="filters"></form>

<div class="table-wrap">
<table>
    <tr>
        <th>ID</th><th>Name</th><th>Species</th><th>Breed</th><th>Age</th>
        <th>Status</th><th>Fee</th><th>Vaccinated</th><th>Adopted by</th><th>Actions</th>
    </tr>
    <tr class="filter-row">
        <?php // aria-label names each box for screen readers, since the column title is in the row above. ?>
        <th><input form="filters" name="id" type="number" min="1" value="<?= $f["id"] ?>" aria-label="Filter by ID"></th>
        <th><input form="filters" name="name" maxlength="50" placeholder="Part of a name" value="<?= htmlspecialchars($f["name"]) ?>" aria-label="Filter by name"></th>
        <th><select form="filters" name="species" aria-label="Filter by species"><?php options($species_list, $f["species"]); ?></select></th>
        <th><select form="filters" name="breed" aria-label="Filter by breed"><?php options([...$breeds, "Unknown"], $f["breed"]); ?></select></th>
        <th><select form="filters" name="age" aria-label="Filter by age"><?php options(array_keys($age_groups), $f["age"]); ?></select></th>
        <th><select form="filters" name="status" aria-label="Filter by status"><?php options($status_list, $f["status"]); ?></select></th>
        <th><select form="filters" name="fee" aria-label="Filter by fee"><?php options($fees, $f["fee"], '$'); ?></select></th>
        <th><select form="filters" name="vaccinated" aria-label="Filter by vaccinated"><?php options(["Yes", "No"], $f["vaccinated"]); ?></select></th>
        <th><select form="filters" name="adopted_by" aria-label="Filter by adopter"><?php options($adopters, $f["adopted_by"]); ?></select></th>
        <th class="actions">
            <button class="button" type="submit" form="filters">Filter</button>
            <a href="index.php">Clear</a>
        </th>
    </tr>
    <?php if ($pets->num_rows === 0): ?>
        <tr><td colspan="10">No pets match these filters.</td></tr>
    <?php endif; ?>
    <?php while ($pet = $pets->fetch_assoc()): ?>
        <?php
        if ($pet["age"] === null)   { $age = "Unknown"; }                     // No birth date and no estimate (only other animals).
        elseif ($pet["age"] == 0)   { $age = "Under 1 year"; }
        else                        { $age = $pet["age"] . ($pet["age"] == 1 ? " year" : " years"); }
        if ($pet["age_is_estimate"]) {                                        // The age is a guess (Business rule #31),
            $age = $pet["age"] == 0 ? $age . " (estimated)" : "About " . $age;   // so it shows like "About 3 years" for Cerberus.
        }
        ?>
        <tr>
            <td><?= $pet["pet_id"] ?></td>
            <td><?= htmlspecialchars($pet["name"]) ?></td>   <?php // htmlspecialchars stops code hidden in a name from running. ?>
            <td><?= $pet["species"] ?></td>
            <td><?= htmlspecialchars($pet["breed"] ?? "Unknown") ?></td>
            <td><?= $age ?></td>
            <td><span class="status <?= $pet["status"] ?>"><?= $pet["status"] ?></span></td>
            <td>$<?= number_format($pet["adoption_fee"], 2) ?></td>
            <td><?= $pet["vaccinated"] ? "Yes" : "No" ?></td>
            <td><?= htmlspecialchars($pet["adopted_by"] ?? "") ?></td>
            <td class="actions">
                <a class="button" href="edit.php?id=<?= $pet["pet_id"] ?>">Edit</a>
                <?php // The form asks "Delete Toto?" first, so one click can't delete by accident. ?>
                <form method="post" action="delete.php"
                      onsubmit="return confirm(<?= htmlspecialchars(json_encode("Delete " . $pet["name"] . "?")) ?>);">
                    <input type="hidden" name="id" value="<?= $pet["pet_id"] ?>">
                    <button class="button danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
