<?php
require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $vat_rate = $_POST['vat_rate'];
    $vat_amount = ($amount * $vat_rate / 100) / (1 + $vat_rate / 100);

    $stmt = $pdo->prepare("INSERT INTO transactions (date, type, category, description, amount, vat_rate, vat_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $type, $category, $description, $amount, $vat_rate, $vat_amount]);

    $message = 'Tapahtuma lisätty onnistuneesti!';
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

    <form method="post" class="surface p-5 sm:p-8">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="date" class="form-label">Päivämäärä</label>
                <input id="date" class="form-control" type="date" name="date" required>
            </div>
            <div>
                <label for="type" class="form-label">Tyyppi</label>
                <select id="type" class="form-control" name="type" required>
                    <option value="income">Tulo</option>
                    <option value="expense">Meno</option>
                </select>
            </div>
            <div>
                <label for="category" class="form-label">Kategoria</label>
                <select id="category" class="form-control" name="category" required>
                    <option value="income">Tulo</option>
                    <option value="general_expense">Yleinen meno</option>
                    <option value="travel">Matkalasku</option>
                    <option value="phone_data">Puhelin ja tietoliikenne</option>
                </select>
            </div>
            <div>
                <label for="amount" class="form-label">Summa (€)</label>
                <input id="amount" class="form-control" type="number" step="0.01" name="amount" required>
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="form-label">Kuvaus</label>
                <input id="description" class="form-control" type="text" name="description" required>
            </div>
            <div>
                <label for="vat_rate" class="form-label">ALV-prosentti</label>
                <input id="vat_rate" class="form-control" type="number" step="0.01" name="vat_rate" value="24">
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border-subtle pt-6">
            <button type="submit" class="button-primary">Tallenna tapahtuma</button>
            <a href="index.php" class="button-secondary">Peruuta</a>
        </div>
    </form>
</div>
<?php require 'partials/footer.php'; ?>
