<?php
require 'config.php';
require 'auth.php';

require_role(WRITE_ROLES);

$message = '';
$errors = [];
$date = '';
$type = '';
$category = '';
$description = '';
$amount = '';
$vat_rate = '24';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $dateInput = $_POST['date'] ?? '';
    $typeInput = $_POST['type'] ?? '';
    $categoryInput = $_POST['category'] ?? '';
    $descriptionInput = $_POST['description'] ?? '';
    $amountInput = $_POST['amount'] ?? '';
    $vatRateInput = $_POST['vat_rate'] ?? '24';

    $date = is_string($dateInput) ? trim($dateInput) : '';
    $type = is_string($typeInput) ? trim($typeInput) : '';
    $category = is_string($categoryInput) ? trim($categoryInput) : '';
    $description = is_string($descriptionInput) ? trim($descriptionInput) : '';
    $amount = is_string($amountInput) ? trim($amountInput) : '';
    $vat_rate = is_string($vatRateInput) ? trim($vatRateInput) : '';
    if ($vat_rate === '') {
        $vat_rate = '0';
    }

    $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $dateValue === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $dateValue->format('Y-m-d') !== $date
    ) {
        $errors[] = 'Anna kelvollinen päivämäärä.';
    }

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Valitse kelvollinen tapahtuman tyyppi.';
    }

    if (!in_array($category, ['income', 'general_expense', 'travel', 'phone_data'], true)) {
        $errors[] = 'Valitse kelvollinen kategoria.';
    }

    if ($description === '' || strlen($description) > 255) {
        $errors[] = 'Kuvauksen tulee olla 1–255 merkkiä pitkä.';
    }

    if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $amount) || (float) $amount <= 0 || (float) $amount > 99999999.99) {
        $errors[] = 'Summan tulee olla positiivinen, enintään kaksi desimaalia sisältävä luku.';
    }

    if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $vat_rate) || (float) $vat_rate > 100) {
        $errors[] = 'ALV-prosentin tulee olla luku väliltä 0–100, enintään kaksi desimaalia.';
    }

    if ($errors === []) {
        $vat_amount = ((float) $amount * (float) $vat_rate) / (100 + (float) $vat_rate);

        $stmt = $pdo->prepare("INSERT INTO transactions (date, type, category, description, amount, vat_rate, vat_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$date, $type, $category, $description, $amount, $vat_rate, $vat_amount]);

        $message = 'Tapahtuma lisätty onnistuneesti!';
        $date = '';
        $type = '';
        $category = '';
        $description = '';
        $amount = '';
        $vat_rate = '24';
    }
}

$pageTitle = 'Lisää tapahtuma';
$activePage = 'add';
require 'partials/header.php';
?>
<div class="mx-auto max-w-3xl">
    <div class="mb-8">
        <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Tapahtumat</p>
        <h1 class="text-3xl font-bold tracking-tight text-ink">Lisää tapahtuma</h1>
        <p class="mt-2 text-slate-500">Kirjaa yrityksen tulo tai meno helposti.</p>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            <ul class="list-inside list-disc">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="surface p-5 sm:p-8">
        <?php echo csrf_field(); ?>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="date" class="form-label">Päivämäärä</label>
                <input id="date" class="form-control" type="date" name="date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="type" class="form-label">Tyyppi</label>
                <select id="type" class="form-control" name="type" required>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Tulo</option>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Meno</option>
                </select>
            </div>
            <div>
                <label for="category" class="form-label">Kategoria</label>
                <select id="category" class="form-control" name="category" required>
                    <option value="income" <?php echo $category === 'income' ? 'selected' : ''; ?>>Tulo</option>
                    <option value="general_expense" <?php echo $category === 'general_expense' ? 'selected' : ''; ?>>Yleinen meno</option>
                    <option value="travel" <?php echo $category === 'travel' ? 'selected' : ''; ?>>Matkalasku</option>
                    <option value="phone_data" <?php echo $category === 'phone_data' ? 'selected' : ''; ?>>Puhelin ja tietoliikenne</option>
                </select>
            </div>
            <div>
                <label for="amount" class="form-label">Summa (€)</label>
                <input id="amount" class="form-control" type="number" min="0.01" max="99999999.99" step="0.01" name="amount" value="<?php echo htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="form-label">Kuvaus</label>
                <input id="description" class="form-control" type="text" maxlength="255" name="description" value="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="vat_rate" class="form-label">ALV-prosentti</label>
                <input id="vat_rate" class="form-control" type="number" min="0" max="100" step="0.01" name="vat_rate" value="<?php echo htmlspecialchars($vat_rate, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border-subtle pt-6">
            <button type="submit" class="button-primary">Tallenna tapahtuma</button>
            <a href="index.php" class="button-secondary">Peruuta</a>
        </div>
    </form>
</div>
<?php require 'partials/footer.php'; ?>
