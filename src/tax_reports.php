<?php
require 'config.php';
require 'auth.php';

require_login();

$exportFiles = [
    'vat' => 'alv_ilmoitus.csv',
    'tax' => 'veroilmoitus.csv',
];

if (isset($_GET['export'])) {
    $type = $_GET['export'];
    if (!is_string($type) || !array_key_exists($type, $exportFiles)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Virheellinen vientityyppi.';
        exit;
    }
    $filename = $exportFiles[$type];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    // CSV headers
    fputcsv($output, ['Päivämäärä', 'Tyyppi', 'Kategoria', 'Kuvaus', 'Summa', 'ALV-prosentti', 'ALV-summa'], escape: '');

    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY date");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['date'],
            $row['type'] == 'income' ? 'Tulo' : 'Meno',
            ucfirst(str_replace('_', ' ', $row['category'])),
            $row['description'],
            $row['amount'],
            $row['vat_rate'],
            $row['vat_amount']
        ], escape: '');
    }

    fclose($output);
    exit;
}

// VAT summary
$stmt = $pdo->query("SELECT SUM(vat_amount) as total_vat FROM transactions WHERE type='income'");
$vat_payable = $stmt->fetch(PDO::FETCH_ASSOC)['total_vat'] ?? 0;
$stmt = $pdo->query("SELECT SUM(vat_amount) as total_vat FROM transactions WHERE type='expense'");
$vat_deductible = $stmt->fetch(PDO::FETCH_ASSOC)['total_vat'] ?? 0;
$vat_balance = $vat_payable - $vat_deductible;

// Tax summary
$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='income'");
$total_income = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='expense'");
$total_expense = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$pageTitle = 'Veroilmoitukset';
$activePage = 'tax';
require 'partials/header.php';
?>
<div class="mb-8">
    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Verotus</p>
    <h1 class="text-3xl font-bold tracking-tight text-ink">Veroilmoitukset</h1>
    <p class="mt-2 text-slate-500">Tarkista verotuksen yhteenveto ja lataa siirtotiedostot.</p>
</div>

<section class="mb-8">
    <div class="mb-4">
        <h2 class="text-xl font-bold text-ink">ALV-ilmoitus</h2>
        <p class="mt-1 text-sm text-slate-500">Arvonlisäveron yhteenveto kirjatuista tapahtumista.</p>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        <article class="metric-card">
            <p class="text-sm font-semibold text-slate-500">ALV maksettava</p>
            <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($vat_payable, 2); ?> €</p>
        </article>
        <article class="metric-card">
            <p class="text-sm font-semibold text-slate-500">ALV vähennettävä</p>
            <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($vat_deductible, 2); ?> €</p>
        </article>
        <article class="metric-card">
            <p class="text-sm font-semibold text-slate-500">ALV-saldo</p>
            <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($vat_balance, 2); ?> €</p>
        </article>
    </div>
</section>

<section class="surface p-5 sm:p-8">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-ink">Veroilmoitus</h2>
        <p class="mt-1 text-sm text-slate-500">Verotettavan tulon laskelma ja CSV-vienti.</p>
    </div>
    <dl class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-slate-50 p-4">
            <dt class="text-sm text-slate-500">Kokonaistulot</dt>
            <dd class="mt-2 text-xl font-bold text-ink"><?php echo number_format($total_income, 2); ?> €</dd>
        </div>
        <div class="rounded-xl bg-slate-50 p-4">
            <dt class="text-sm text-slate-500">Kokonaismenot</dt>
            <dd class="mt-2 text-xl font-bold text-ink"><?php echo number_format($total_expense, 2); ?> €</dd>
        </div>
        <div class="rounded-xl bg-brand-light p-4">
            <dt class="text-sm text-slate-500">Verotettava tulo</dt>
            <dd class="mt-2 text-xl font-bold text-ink"><?php echo number_format($total_income - $total_expense, 2); ?> €</dd>
        </div>
    </dl>
    <div class="mt-8 flex flex-wrap gap-3 border-t border-border-subtle pt-6">
        <a href="?export=vat" class="button-primary">Vie ALV-ilmoitus CSV:ään</a>
        <a href="?export=tax" class="button-secondary">Vie veroilmoitus CSV:ään</a>
    </div>
</section>
<?php require 'partials/footer.php'; ?>
