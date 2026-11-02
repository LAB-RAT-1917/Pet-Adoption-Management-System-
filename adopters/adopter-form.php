<?php
// adopter-form.php
// Purpose: The error box and adopter fields that add.php and edit.php share, so both forms look and work the same.
// The page that loads this file must set $adopter (the values to show) and $errors (the problems to show).
?>
<p class="note"><span class="required">*</span> Required (Business rule #6)</p>   <!-- Tells staff what the red stars mean. -->

<?php if ($errors): ?>                              <!-- Only shows when something needs fixing. -->
    <div class="message error">
        <p>Please fix these:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php // Every box is required (Business rule #6). The red star shows it, and "required" makes the browser refuse an empty box. ?>
<?php // htmlspecialchars keeps what was typed safe to show again after an error. ?>
<label for="first_name">First name <span class="required">*</span></label>
<input id="first_name" name="first_name" maxlength="50" required value="<?= htmlspecialchars($adopter["first_name"]) ?>">

<label for="last_name">Last name <span class="required">*</span></label>
<input id="last_name" name="last_name" maxlength="50" required value="<?= htmlspecialchars($adopter["last_name"]) ?>">

<label for="phone">Phone <span class="required">*</span></label>
<input id="phone" name="phone" type="tel" maxlength="20" required placeholder="(718) 555-0123"
       value="<?= htmlspecialchars($adopter["phone"]) ?>">   <!-- type="tel" brings up the number pad on a phone. -->

<label for="email">Email <span class="required">*</span></label>
<input id="email" name="email" type="email" maxlength="100" required placeholder="name@example.com"
       value="<?= htmlspecialchars($adopter["email"]) ?>">   <!-- No two adopters can share an email (Business rule #7). -->

<label for="address">Address <span class="required">*</span></label>
<input id="address" name="address" maxlength="150" required placeholder="Street, city, state and zip code"
       value="<?= htmlspecialchars($adopter["address"]) ?>">

<label for="date_of_birth">Date of birth <span class="required">*</span></label>
<input id="date_of_birth" name="date_of_birth" type="date" min="1900-01-01" max="<?= date("Y-m-d") ?>" required
       value="<?= htmlspecialchars($adopter["date_of_birth"]) ?>">   <!-- The calendar can't pick a future date. -->
<p class="note">Adopters must be 18 or older to adopt. Younger people can be added, but their applications are rejected (Business rule #14).</p>
