<?php
// applications/rejected.php
// Purpose: The Rejected applications list, with the reason and the rule each one broke (Business rule #25).
// Reads the rejected_applications view (applications joined with adopters, pets, employees and rejection_reasons).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Shared connection ($conn).

// 2. GET THE DATA
$rows = $conn->query("SELECT * FROM rejected_applications")->fetch_all(MYSQLI_ASSOC);   // Every Rejected application, with names and reasons.

// Message sent back by new.php when an application breaks a rule.
$msg  = $_GET["msg"]  ?? "";
$type = ($_GET["type"] ?? "") === "error" ? "error" : "success";

// 3. SHOW THE PAGE
$page_title = "Rejected Applications";
$active     = "applications";
$tab        = "rejected";
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/nav.php";
?>
<h1>Rejected Applications</h1>

<?php if ($msg !== ""): ?>
    <p class="message <?= $type ?>"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if (!$rows): ?>
    <p>There are no rejected applications.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Adopter</th>
                    <th>Pet</th>
                    <th>Reason</th>
                    <th>Rule broken</th>
                    <th>Handled by</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= (int) $row["application_id"] ?></td>
                        <td><?= htmlspecialchars(date("M j, Y", strtotime($row["application_date"]))) ?></td>
                        <td><?= htmlspecialchars($row["adopter_name"]) ?></td>
                        <td><?= htmlspecialchars($row["pet_name"]) ?></td>
                        <td><?= htmlspecialchars($row["reason"]) ?></td>
                        <td><?= htmlspecialchars($row["rule_broken"]) ?></td>
                        <td><?= htmlspecialchars($row["handled_by"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
