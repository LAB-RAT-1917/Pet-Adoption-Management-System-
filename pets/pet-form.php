<?php
// pet-form.php
// Project: Pet Adoption Management System (CIS 344 Group Project).
// Purpose: The error box and pet fields that add.php and edit.php share, so both forms look and work the same.
// The page that loads this file must set $pet (the values to show), $errors (the problems to show)
// and $arrival_date (today for a new pet, or the day the pet arrived).
?>
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

<label for="name">Name</label>
<input id="name" name="name" maxlength="50" required value="<?= htmlspecialchars($pet["name"]) ?>">   <!-- Keeps what was typed if there's an error. -->

<label for="species">Species</label>
<select id="species" name="species" required>
    <option value="">Pick one</option>
    <?php foreach (["Dog", "Cat", "Rabbit", "Bird", "Other"] as $s): ?>
        <option value="<?= $s ?>"<?= $s === $pet["species"] ? " selected" : "" ?>><?= $s ?></option>
    <?php endforeach; ?>
</select>

<label for="breed">Breed (optional)</label>
<input id="breed" name="breed" maxlength="50" value="<?= htmlspecialchars($pet["breed"] ?? "") ?>">

<label for="birth_date">Birth date (if known)</label>
<input id="birth_date" name="birth_date" type="date" max="<?= $arrival_date ?>" value="<?= htmlspecialchars($pet["birth_date"] ?? "") ?>">   <!-- The calendar can't pick a date after the pet arrived. -->

<label for="estimated_age">Estimated age when it arrived (years)</label>
<?php // Only for a pet with no birth date. The script at the bottom turns this box off when a birth date is picked (Business rule #31). ?>
<?php // onkeydown blocks e, +, - and the dot, so only whole numbers can be typed. ?>
<input id="estimated_age" name="estimated_age" type="number" min="0" max="50" step="1"
       onkeydown="return !['e', 'E', '+', '-', '.'].includes(event.key);"
       value="<?= htmlspecialchars((string) ($pet["estimated_age"] ?? "")) ?>">
<p class="note">Only needed when the birth date is unknown. Use 0 for under 1 year.</p>

<label for="adoption_fee">Adoption fee ($)</label>
<?php // Dogs, cats, rabbits and birds get their set price filled in by the script at the bottom. Only other animals type a fee (Business rules #29 and #32). ?>
<?php // onkeydown blocks e, + and -, which number boxes normally allow, so only numbers can be typed. ?>
<input id="adoption_fee" name="adoption_fee" type="number" step="0.01" required
       onkeydown="return !['e', 'E', '+', '-'].includes(event.key);"
       value="<?= htmlspecialchars((string) ($pet["adoption_fee"] ?? "")) ?>">
<p class="note" id="fee_note">Pick a species to see the price.</p>              <!-- The script below changes this message. -->

<table class="price-table">                                                    <!-- The set prices, so staff can see where the fee comes from. -->
    <tr><th>Set prices</th><th>Under 1 year</th><th>1 to 6 years</th><th>7 and up</th></tr>
    <?php foreach (pet_set_prices() as $species_name => [$young, $adult, $senior]): ?>
        <tr><td><?= $species_name ?></td><td>$<?= $young ?></td><td>$<?= $adult ?></td><td>$<?= $senior ?></td></tr>
    <?php endforeach; ?>
    <tr><td>Other</td><td colspan="3">Staff type a fee from $10 to $75</td></tr>
</table>
<p class="note">Age counts on the day the pet arrives (Business rule #30). If the birth date is unknown, the estimated age sets the price (Business rule #31).</p>

<fieldset class="yes-no">                         <!-- Groups the Yes and No choices under one question. -->
    <legend>Is the pet vaccinated?</legend>
    <label class="choice">
        <input type="radio" name="vaccinated" value="1" required<?= (string) $pet["vaccinated"] === "1" ? " checked" : "" ?>> Yes
    </label>
    <label class="choice">
        <input type="radio" name="vaccinated" value="0"<?= (string) $pet["vaccinated"] === "0" ? " checked" : "" ?>> No
    </label>
    <?php // Neither is picked at first, so staff have to answer. A pet must be vaccinated before anyone can apply (Business rule #13). ?>
</fieldset>

<script>
// Fills in the set price when staff pick a species, a birth date or an estimated age (Business rules #29 to #31).
// The server works out the same price when the form is saved, so this script is only a helper.
const prices   = <?= json_encode(pet_set_prices()) ?>;         // The same price table as check-pet.php.
const arrival  = "<?= $arrival_date ?>";                         // Today for a new pet, or the day it arrived.
const species  = document.getElementById("species");
const birth    = document.getElementById("birth_date");
const estimate = document.getElementById("estimated_age");
const fee      = document.getElementById("adoption_fee");
const note     = document.getElementById("fee_note");

function ageOnArrival() {                                        // Full years old on the day it arrived, or null if unknown (Business rule #30).
    if (!birth.value) {                                          // No birth date, so use the estimate (Business rule #31).
        return estimate.value === "" ? null : Number(estimate.value);
    }
    const b = new Date(birth.value), a = new Date(arrival);
    let years = a.getUTCFullYear() - b.getUTCFullYear();         // Count the years,
    if (a.getUTCMonth() < b.getUTCMonth() ||
        (a.getUTCMonth() === b.getUTCMonth() && a.getUTCDate() < b.getUTCDate())) years--;   // minus 1 if its birthday hadn't come yet that year.
    return years;
}

function updateAgeBox() {                                        // The estimate box is only for a pet with no birth date (Business rule #31).
    if (birth.value) {
        estimate.value = "";                                     // A birth date wins, so clear the estimate
        estimate.disabled = true;                                // and turn the box off. A box that's off isn't sent with the form.
    } else {
        estimate.disabled = false;
    }
    estimate.required = !birth.value && !!prices[species.value];   // Dogs, cats, rabbits and birds must have an age.
}

function updateFee() {
    const groups = ["under 1 year", "1 to 6 years", "7 and up"];
    if (prices[species.value]) {                                 // Dog, cat, rabbit or bird: show the set price.
        fee.readOnly = true;                                     // Staff can't type over a set price.
        fee.removeAttribute("min");
        fee.removeAttribute("max");
        const age = ageOnArrival();
        if (age === null) {                                      // No birth date and no estimate yet.
            fee.value = "";
            note.textContent = "Type the birth date, or an estimated age if it's unknown, to see the price.";
            return;
        }
        const g = age < 1 ? 0 : (age <= 6 ? 1 : 2);              // Which column of the price list.
        fee.value = prices[species.value][g];
        note.textContent = "Set price for a " + species.value.toLowerCase() + " aged " + groups[g]
                         + (birth.value ? "." : " (from the estimate).");
    } else if (species.value === "Other") {                      // Other animals: staff type the fee.
        if (fee.readOnly) fee.value = "";                        // Clear a set price left over from another species.
        fee.readOnly = false;
        fee.min = 10;                                            // The browser also checks $10 to $75.
        fee.max = 75;
        note.textContent = "Type a fee from $10 to $75. Numbers only.";
    } else {                                                     // No species picked yet:
        fee.value = "";                                          // nothing to show,
        fee.readOnly = true;                                     // and nothing to type until a species is picked.
        note.textContent = "Pick a species to see the price.";
    }
}

function updateAll() {                                           // Runs both helpers, in order.
    updateAgeBox();
    updateFee();
}

species.addEventListener("change", updateAll);                   // Run when the species changes,
birth.addEventListener("change", updateAll);                     // when the birth date changes,
estimate.addEventListener("input", updateAll);                   // while an estimate is typed,
updateAll();                                                     // and once when the page opens.
</script>
