<?php
// adopters/index.php
// Purpose: Lists every adopter by ID, name and phone, with a filter box under each column. Links to view, add, edit and delete an adopter.
// Adopters are personal data, so the list shows only the ID, name and phone. The rest shows only on each adopter's own page (Adopters requirement #5).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";

// 2. THE ORDER CHOICES
// Each choice => the piece it adds to ORDER BY. ORDER BY can't take a ? like the filters can,
// so only these set pieces can ever go into the SQL.
$name_orders = [
    "A to Z" => "first_name, last_name",                         // The way names show: first name, then last name.
    "Z to A" => "first_name DESC, last_name DESC",
];
$phone_orders = [
    "Low to high" => "REGEXP_REPLACE(phone, '[^0-9]', '')",      // Only the numbers count, so (718) 555-0123 and 718-555-0123 sort the same.
    "High to low" => "REGEXP_REPLACE(phone, '[^0-9]', '') DESC",
];

// 3. READ THE FILTERS
// Each filter comes from the address, like ?name=ross&phone_order=Low+to+high. "" means "show all".
// Anything that isn't on its list is thrown out, so a changed address can't break the page (Adopters requirement #6).
function get_filter(string $key): string                         // A list or anything that isn't text counts as "".
{
    $value = $_GET[$key] ?? "";
    return is_string($value) ? trim($value) : "";
}
$f = [];
$id = filter_var(get_filter("id"), FILTER_VALIDATE_INT);         // Negatives are fine, because musicians have negative IDs.
$f["id"]          = $id === false ? "" : $id;
$f["name"]        = mb_substr(get_filter("name"), 0, 101);
$f["name_order"]  = isset($name_orders[get_filter("name_order")]) ? get_filter("name_order") : "A to Z";
$f["phone_order"] = isset($phone_orders[get_filter("phone_order")]) ? get_filter("phone_order") : "";

// 4. GET THE ADOPTERS THAT MATCH
// Each filter that's set adds one test, and each ? gets its value from $params, in order.
$where  = [];
$params = [];
if ($f["id"] !== "")   { $where[] = "adopter_id = ?";                                              $params[] = $f["id"]; }
if ($f["name"] !== "") { $where[] = "CONCAT(first_name, ' ', last_name) LIKE CONCAT('%', ?, '%')"; $params[] = $f["name"]; }

// A phone order goes first when it's picked. The name order comes next, so adopters who share a phone still sort by name.
$order = ($f["phone_order"] !== "" ? $phone_orders[$f["phone_order"]] . ", " : "") . $name_orders[$f["name_order"]];

$sql = "SELECT adopter_id, first_name, last_name, phone FROM adopters"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")
     . " ORDER BY " . $order;
$stmt = $conn->prepare($sql);                                    // Prepared statement, so typed filters can't change the SQL.
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);   // One "s" (text) per value. "..." gives each value its own ?.
}
$stmt->execute();
$adopters = $stmt->get_result();
$total = $conn->query("SELECT COUNT(*) FROM adopters")->fetch_row()[0];   // For "Showing 1 of 29 adopters."

// order_options() prints the choices for one order menu, and keeps the current choice picked after the page reloads.
// $none is the text for "no order", like "Any order". Leave it out when the menu always has a choice.
function order_options(array $orders, string $current, string $none = ""): void
{
    if ($none !== "") {
        echo '<option value="">' . $none . '</option>';
    }
    foreach (array_keys($orders) as $choice) {                     // Set text, like "A to Z", so it's safe to print.
        $selected = $choice === $current ? " selected" : "";
        echo '<option value="' . $choice . '"' . $selected . '>' . $choice . '</option>';
    }
}

// 5. MESSAGE FROM THE ADD, EDIT OR DELETE PAGE
// Only these set messages can show.
$messages = [
    "added"   => ["success", "The adopter was added."],
    "updated" => ["success", "The adopter was updated."],
    "deleted" => ["success", "The adopter was deleted."],
    "in-use"  => ["error",   "That adopter has applications, so they can't be deleted (Business rule #17)."],
    "missing" => ["error",   "That adopter wasn't found. They may have been deleted already."],
];
$done = $messages[get_filter("done")] ?? null;

// 6. SHOW THE PAGE
$page_title = "Adopters";
$active     = "adopters";
require_once __DIR__ . "/../includes/header.php";
?>
<h1>Adopters</h1>

<?php if ($done): ?>
    <p class="message <?= $done[0] ?>"><?= $done[1] ?></p>
<?php endif; ?>

<div class="list-top">
    <a class="button" href="add.php">Add an adopter</a>
    <span><?= $where ? "Showing " . $adopters->num_rows . " of " . $total . " adopters." : "Showing all " . $total . " adopters." ?></span>
</div>

<?php // The filter boxes sit inside the table, and form="filters" ties each one to this form. ?>
<?php // That way each row's Delete form stays separate, because a form can't go inside another form. ?>
<form method="get" id="filters"></form>

<div class="table-wrap">
<table>
    <tr><th class="id-col">ID</th><th>Name</th><th>Phone</th><th>Actions</th></tr>
    <tr class="filter-row">
        <?php // aria-label names each box for screen readers, since the column title is in the row above. ?>
        <th><input form="filters" name="id" type="number" class="id-box" value="<?= $f["id"] ?>" aria-label="Filter by ID"></th>
        <th>
            <div class="filter-pair">
                <input form="filters" name="name" maxlength="101" placeholder="Part of a name" value="<?= htmlspecialchars($f["name"]) ?>" aria-label="Filter by name">
                <select form="filters" name="name_order" aria-label="Name order"><?php order_options($name_orders, $f["name_order"]); ?></select>
            </div>
        </th>
        <th><select form="filters" name="phone_order" aria-label="Phone order"><?php order_options($phone_orders, $f["phone_order"], "Any order"); ?></select></th>
        <th class="actions">
            <button class="button" type="submit" form="filters">Filter</button>
            <a href="index.php">Clear</a>
        </th>
    </tr>
    <?php if ($adopters->num_rows === 0): ?>
        <tr><td colspan="4">No adopters match these filters.</td></tr>
    <?php endif; ?>
    <?php while ($adopter = $adopters->fetch_assoc()): ?>
        <?php $full_name = $adopter["first_name"] . " " . $adopter["last_name"]; ?>
        <tr>
            <td><?= $adopter["adopter_id"] ?></td>
            <td><?= htmlspecialchars($full_name) ?></td>     <?php // htmlspecialchars stops code hidden in a name from running. ?>
            <td class="no-wrap"><?= htmlspecialchars($adopter["phone"]) ?></td>
            <td class="actions">
                <a class="button" href="view.php?id=<?= $adopter["adopter_id"] ?>">View</a>
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
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
