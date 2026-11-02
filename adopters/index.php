<?php
// adopters/index.php
// Purpose: Lists every adopter by ID, name and phone, with a filter box under each column. Links to view, add, edit and delete an adopter.
// Adopters are personal data, so the list shows only the ID, name and phone. The rest shows only on each adopter's own page (Adopters requirement #5).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Load the shared connection ($conn). ".." means one folder up.

// 2. THE ORDER CHOICES
// Each choice => the piece it adds to ORDER BY. ORDER BY can't take a ? like the filters can,
// so only these set pieces can ever go into the SQL.
$name_orders = [
    "A to Z" => "first_name, last_name",                         // The way names show: first name, then last name.
    "Z to A" => "first_name DESC, last_name DESC",               // DESC = the other way around.
];
$phone_orders = [
    "Low to high" => "REGEXP_REPLACE(phone, '[^0-9]', '')",      // Only the numbers count, so (718) 555-0123 and 718-555-0123 sort the same.
    "High to low" => "REGEXP_REPLACE(phone, '[^0-9]', '') DESC",
];

// 3. READ THE FILTERS
// Each filter comes from the address, like ?name=ross&phone_order=Low+to+high. "" means "show all".
// Anything that isn't on its list is thrown out, so a changed address can't break the page (Adopters requirement #6).
function get_filter(string $key): string                         // Reads one filter. A list or anything that isn't text counts as "".
{
    $value = $_GET[$key] ?? "";
    return is_string($value) ? trim($value) : "";
}
$f = [];                                                         // The filters, by column.
$id = filter_var(get_filter("id"), FILTER_VALIDATE_INT);         // A whole number, or false. Negatives are fine, because musicians have negative IDs.
$f["id"]          = $id === false ? "" : $id;                    // "" = no ID filter.
$f["name"]        = mb_substr(get_filter("name"), 0, 101);       // Up to 101 letters, a full first and last name.
$f["name_order"]  = isset($name_orders[get_filter("name_order")]) ? get_filter("name_order") : "A to Z";   // A to Z unless Z to A was picked.
$f["phone_order"] = isset($phone_orders[get_filter("phone_order")]) ? get_filter("phone_order") : "";      // "" = Any order.

// 4. GET THE ADOPTERS THAT MATCH
// Each filter that's set adds one test, and each ? gets its value from $params, in order.
// Without this, the page couldn't mix filters, like part of a name + an ID.
$where  = [];                                                    // The tests, like "adopter_id = ?".
$params = [];                                                    // The values for the ? marks.
if ($f["id"] !== "")   { $where[] = "adopter_id = ?";                                              $params[] = $f["id"]; }
if ($f["name"] !== "") { $where[] = "CONCAT(first_name, ' ', last_name) LIKE CONCAT('%', ?, '%')"; $params[] = $f["name"]; }   // Part of a name, like "ross".

// A phone order goes first when it's picked. The name order comes next, so adopters who share a phone still sort by name.
$order = ($f["phone_order"] !== "" ? $phone_orders[$f["phone_order"]] . ", " : "") . $name_orders[$f["name_order"]];

$sql = "SELECT adopter_id, first_name, last_name, phone FROM adopters"   // Only what the list shows (Adopters requirement #5).
     . ($where ? " WHERE " . implode(" AND ", $where) : "")             // AND means an adopter must pass every filter.
     . " ORDER BY " . $order;                                           // A set piece from the lists above, never typed text.
$stmt = $conn->prepare($sql);                                    // Prepared statement, so typed filters can't change the SQL.
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);   // One "s" (text) per value. "..." gives each value its own ?.
}
$stmt->execute();                                                // Run the query.
$adopters = $stmt->get_result();                                 // The rows to show in the table.
$total = $conn->query("SELECT COUNT(*) FROM adopters")->fetch_row()[0];   // Every adopter, for "Showing 1 of 29 adopters."

// order_options() prints the choices for one order menu, and keeps the current choice picked after the page reloads.
// $none is the text for "no order", like "Any order". Leave it out when the menu always has a choice.
function order_options(array $orders, string $current, string $none = ""): void
{
    if ($none !== "") {
        echo '<option value="">' . $none . '</option>';             // "" = this menu doesn't change the order.
    }
    foreach (array_keys($orders) as $choice) {                     // Only the names, like "A to Z". They're set text, so they're safe to print.
        $selected = $choice === $current ? " selected" : "";       // The choice that's in use right now.
        echo '<option value="' . $choice . '"' . $selected . '>' . $choice . '</option>';
    }
}

// 5. MESSAGE FROM THE ADD, EDIT OR DELETE PAGE
// Without this, staff wouldn't know if their change worked. Only these set messages can show.
$messages = [
    "added"   => ["success", "The adopter was added."],
    "updated" => ["success", "The adopter was updated."],
    "deleted" => ["success", "The adopter was deleted."],
    "in-use"  => ["error",   "That adopter has applications, so they can't be deleted (Business rule #17)."],
    "missing" => ["error",   "That adopter wasn't found. They may have been deleted already."],
];
$done = $messages[get_filter("done")] ?? null;                   // Pick the message for ?done=..., or none.

// 6. SHOW THE PAGE
$page_title = "Adopters";                                        // Tab title.
$active     = "adopters";                                        // Highlight Adopters in the menu.
require_once __DIR__ . "/../includes/header.php";                // Top of the page: menu and CSS.
?>
<h1>Adopters</h1>

<?php if ($done): ?>
    <p class="message <?= $done[0] ?>"><?= $done[1] ?></p>    <!-- Green for success, red for an error. -->
<?php endif; ?>

<div class="list-top">                                        <!-- Add button and the count side by side. -->
    <a class="button" href="add.php">Add an adopter</a>
    <span><?= $where ? "Showing " . $adopters->num_rows . " of " . $total . " adopters." : "Showing all " . $total . " adopters." ?></span>
</div>

<?php // The filter boxes sit inside the table, and form="filters" ties each one to this form. ?>
<?php // That way each row's Delete form stays separate, because a form can't go inside another form. ?>
<form method="get" id="filters"></form>                       <!-- GET puts the filters in the address, like ?name=ross. -->

<div class="table-wrap">                                      <!-- Lets the table scroll sideways on a phone. -->
<table>
    <tr><th class="id-col">ID</th><th>Name</th><th>Phone</th><th>Actions</th></tr>   <!-- id-col keeps the ID column narrow. -->
    <tr class="filter-row">                                   <!-- One filter box under each column title. -->
        <?php // aria-label names each box for screen readers, since the column title is in the row above. ?>
        <th><input form="filters" name="id" type="number" class="id-box" value="<?= $f["id"] ?>" aria-label="Filter by ID"></th>
        <th>
            <div class="filter-pair">                         <!-- The name search and its order menu side by side. -->
                <input form="filters" name="name" maxlength="101" placeholder="Part of a name" value="<?= htmlspecialchars($f["name"]) ?>" aria-label="Filter by name">
                <select form="filters" name="name_order" aria-label="Name order"><?php order_options($name_orders, $f["name_order"]); ?></select>
            </div>
        </th>
        <th><select form="filters" name="phone_order" aria-label="Phone order"><?php order_options($phone_orders, $f["phone_order"], "Any order"); ?></select></th>
        <th class="actions">
            <button class="button" type="submit" form="filters">Filter</button>
            <a href="index.php">Clear</a>                     <!-- Opens the page with no filters, A to Z by name. -->
        </th>
    </tr>
    <?php if ($adopters->num_rows === 0): ?>
        <tr><td colspan="4">No adopters match these filters.</td></tr>
    <?php endif; ?>
    <?php while ($adopter = $adopters->fetch_assoc()):   // One table row per adopter. ?>
        <?php $full_name = $adopter["first_name"] . " " . $adopter["last_name"]; ?>
        <tr>
            <td><?= $adopter["adopter_id"] ?></td>
            <td><?= htmlspecialchars($full_name) ?></td>     <?php // htmlspecialchars stops code hidden in a name from running. ?>
            <td class="no-wrap"><?= htmlspecialchars($adopter["phone"]) ?></td>   <?php // no-wrap keeps the number on one line on a phone. ?>
            <td class="actions">
                <a class="button" href="view.php?id=<?= $adopter["adopter_id"] ?>">View</a>   <?php // The full details, on their own page. ?>
                <a class="button" href="edit.php?id=<?= $adopter["adopter_id"] ?>">Edit</a>
                <?php // The form asks "Delete Bob Ross?" first, so one click can't delete by accident. ?>
                <form method="post" action="delete.php"
                      onsubmit="return confirm(<?= htmlspecialchars(json_encode("Delete " . $full_name . "?")) ?>);">
                    <input type="hidden" name="id" value="<?= $adopter["adopter_id"] ?>">
                    <button class="button danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</div>
<?php require_once __DIR__ . "/../includes/footer.php";   // Bottom of the page. ?>
