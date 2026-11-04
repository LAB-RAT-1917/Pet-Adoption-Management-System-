<?php
// view.php
// Purpose: One adopter's own page. Shows their full details and every application they made (Adopters requirement #5).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";

// 2. FIND THE ADOPTER
// The ID comes from the address, like view.php?id=1. Musicians have negative IDs, like -110989116, so negatives are allowed.
$id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);   // A whole number, or false if it isn't one.
$stmt = $conn->prepare(
    "SELECT adopter_id, first_name, last_name, phone, email, address, date_of_birth, date_joined,
            TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age
     FROM adopters WHERE adopter_id = ?"           // TIMESTAMPDIFF works out their age today in full years.
);
$stmt->bind_param("i", $id);
$stmt->execute();
$adopter = $stmt->get_result()->fetch_assoc();     // null if there's no one with that ID.

// 3. GET THEIR APPLICATIONS
// One application stores only ID numbers. The JOINs swap each one for a name.
// INNER JOIN = every application has a pet and an employee. LEFT JOIN = only rejected ones have a reason (Business rule #25).
$applications = null;
if ($adopter) {
    $stmt = $conn->prepare(
        "SELECT a.application_id, a.application_date, a.status, p.name AS pet_name, p.species,
                CONCAT(e.first_name, ' ', e.last_name) AS handled_by, r.reason
         FROM applications a
         INNER JOIN pets p      ON p.pet_id = a.pet_id
         INNER JOIN employees e ON e.employee_id = a.employee_id
         LEFT JOIN rejection_reasons r ON r.reason_id = a.rejection_reason_id
         WHERE a.adopter_id = ?
         ORDER BY a.application_date DESC"         // Newest first.
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $applications = $stmt->get_result();
}

// 4. SHOW THE PAGE
$page_title = $adopter ? $adopter["first_name"] . " " . $adopter["last_name"] : "Adopter not found";
$active     = "adopters";
require_once __DIR__ . "/../includes/header.php";
?>
<?php if (!$adopter): ?>
    <h1>Adopter not found</h1>
    <p class="message error">There's no adopter with that ID.</p>
    <p><a href="index.php">Back to the adopter list</a></p>
<?php else: ?>
    <h1><?= htmlspecialchars($adopter["first_name"] . " " . $adopter["last_name"]) ?></h1>

    <table class="details">
        <tr><th>Adopter ID</th><td><?= $adopter["adopter_id"] ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($adopter["phone"]) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($adopter["email"]) ?></td></tr>
        <tr><th>Address</th><td><?= htmlspecialchars($adopter["address"]) ?></td></tr>
        <tr><th>Date of birth</th><td><?= date("M j, Y", strtotime($adopter["date_of_birth"])) ?> (age <?= $adopter["age"] ?>)</td></tr>
        <tr><th>Joined</th><td><?= date("M j, Y", strtotime($adopter["date_joined"])) ?></td></tr>
    </table>
    <?php if ($adopter["age"] < 18): ?>
        <p class="message error">Under 18, so any application is rejected (Business rule #14).</p>
    <?php endif; ?>

    <div class="form-buttons">
        <a class="button" href="edit.php?id=<?= $adopter["adopter_id"] ?>">Edit</a>
        <a href="index.php">Back to the adopter list</a>
    </div>

    <h2>Applications</h2>
    <?php if ($applications->num_rows === 0): ?>
        <p>No applications yet.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <tr><th>ID</th><th>Date</th><th>Pet</th><th>Status</th><th>Reason</th><th>Handled by</th></tr>
            <?php while ($app = $applications->fetch_assoc()): ?>
                <tr>
                    <td><?= $app["application_id"] ?></td>
                    <td><?= date("M j, Y", strtotime($app["application_date"])) ?></td>
                    <td><?= htmlspecialchars($app["pet_name"]) ?> (<?= $app["species"] ?>)</td>
                    <td><span class="status <?= $app["status"] ?>"><?= $app["status"] ?></span></td>
                    <td><?= htmlspecialchars($app["reason"] ?? "") ?></td>   <?php // Blank unless the application was rejected. ?>
                    <td><?= htmlspecialchars($app["handled_by"]) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
