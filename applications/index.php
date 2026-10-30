<?php
// applications/index.php
// Purpose: The Pending applications list. The main menu's Applications link opens this page.
// Reads the pending_applications view (applications joined with adopters, pets and employees).
// Each row has an Approve button, and a Reject button with a staff reason (Business rules #26 and #27).
// Both buttons send the form to process.php.

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Shared connection ($conn).

// 2. GET THE DATA
$rows = $conn->query("SELECT * FROM pending_applications")->fetch_all(MYSQLI_ASSOC);   // Every Pending application, with names.

// Only the two reasons staff choose after review. Reasons 1 to 6 are set by new.php automatically.
$reasons = $conn->query(
    "SELECT reason_id, reason FROM rejection_reasons WHERE rule_number IN (26, 27) ORDER BY reason_id"
)->fetch_all(MYSQLI_ASSOC);

// Message sent back by new.php or process.php, like "Application #52 was approved."
$msg  = $_GET["msg"]  ?? "";
$type = ($_GET["type"] ?? "") === "error" ? "error" : "success";   // Only two allowed values.

// 3. SHOW THE PAGE
$page_title = "Pending Applications";
$active     = "applications";                     // Highlight Applications in the menu.
$tab        = "pending";                          // Highlight Pending in the tabs.
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/nav.php";
?>
<h1>Pending Applications</h1>

<?php if ($msg !== ""): ?>
    <p class="message <?= $type ?>"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if (!$rows): ?>
    <p>There are no pending applications.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Adopter</th>
                    <th>Phone</th>
                    <th>Pet</th>
                    <th>Species</th>
                    <th>Handled by</th>
                    <th>Decision</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $id = (int) $row["application_id"]; ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= htmlspecialchars(date("M j, Y", strtotime($row["application_date"]))) ?></td>
                        <td><?= htmlspecialchars($row["adopter_name"]) ?></td>
                        <td><?= htmlspecialchars($row["adopter_phone"]) ?></td>
                        <td><?= htmlspecialchars($row["pet_name"]) ?></td>
                        <td><?= htmlspecialchars($row["species"]) ?></td>
                        <td><?= htmlspecialchars($row["handled_by"]) ?></td>
                        <td class="decision">
                            <!-- Approve: the application becomes Approved and the pet becomes Adopted. -->
                            <form method="post" action="process.php"
                                  onsubmit="return confirm('Approve application #<?= $id ?>?');">
                                <input type="hidden" name="application_id" value="<?= $id ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="button">Approve</button>
                            </form>

                            <!-- Reject: staff must pick a reason first. -->
                            <form method="post" action="process.php"
                                  onsubmit="return confirm('Reject application #<?= $id ?>?');">
                                <input type="hidden" name="application_id" value="<?= $id ?>">
                                <input type="hidden" name="action" value="reject">
                                <select name="reason_id" required aria-label="Reason for rejecting application <?= $id ?>">
                                    <option value="">Reason...</option>
                                    <?php foreach ($reasons as $r): ?>
                                        <option value="<?= (int) $r["reason_id"] ?>"><?= htmlspecialchars($r["reason"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="button danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
