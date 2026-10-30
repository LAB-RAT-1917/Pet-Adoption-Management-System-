<?php
// applications/approved.php
// Purpose: The Approved applications list.
// Reads the approved_applications view (applications joined with adopters, pets and employees).

// 1. CONNECT
require_once __DIR__ . "/../includes/db.php";   // Shared connection ($conn).

// 2. GET THE DATA
$rows = $conn->query("SELECT * FROM approved_applications")->fetch_all(MYSQLI_ASSOC);   // Every Approved application, with names.

// 3. SHOW THE PAGE
$page_title = "Approved Applications";
$active     = "applications";
$tab        = "approved";
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/nav.php";
?>
<h1>Approved Applications</h1>

<?php if (!$rows): ?>
    <p>There are no approved applications yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Adopter</th>
                    <th>Pet</th>
                    <th>Species</th>
                    <th>Fee</th>
                    <th>Pet status</th>
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
                        <td><?= htmlspecialchars($row["species"]) ?></td>
                        <td>$<?= number_format((float) $row["adoption_fee"], 2) ?></td>
                        <!-- Adopted, or Returned if the pet came back (Business rule #18). -->
                        <td><span class="status <?= htmlspecialchars($row["pet_status"]) ?>"><?= htmlspecialchars($row["pet_status"]) ?></span></td>
                        <td><?= htmlspecialchars($row["handled_by"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
