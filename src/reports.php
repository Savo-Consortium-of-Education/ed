<?php
require 'config.php';
require 'auth.php';

require_login();

// Profitability
$stmt = $pdo->query("SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as total_expense FROM transactions");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_income = $row['total_income'] ?? 0;
$total_expense = $row['total_expense'] ?? 0;
$profit = $total_income - $total_expense;

// Quarterly reports
$quarters = [];
for ($q = 1; $q <= 4; $q++) {
    $start_month = ($q - 1) * 3 + 1;
    $end_month = $q * 3;
    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense FROM transactions WHERE MONTH(date) BETWEEN ? AND ?");
    $stmt->execute([$start_month, $end_month]);
    $quarters[$q] = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = 'Raportit';
$activePage = 'reports';
require 'partials/header.php';
?>
<div class="mb-8">
    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Analytiikka</p>
    <h1 class="text-3xl font-bold tracking-tight text-ink">Raportit</h1>
    <p class="mt-2 text-slate-500">Seuraa yrityksen kannattavuutta ja neljännesvuosittaista kehitystä.</p>
</div>

<section class="mb-8 grid gap-4 md:grid-cols-3">
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">Kokonaistulot</p>
        <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($total_income, 2); ?> €</p>
    </article>
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">Kokonaismenot</p>
        <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($total_expense, 2); ?> €</p>
    </article>
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">Voittomarginaali</p>
        <p class="mt-3 text-2xl font-bold <?php echo $profit >= 0 ? 'text-emerald-600' : 'text-rose-600'; ?>"><?php echo number_format($profit, 2); ?> €</p>
    </article>
</section>

<section class="surface overflow-hidden">
    <div class="border-b border-[#d7dbe8] px-5 py-5">
        <h2 class="text-xl font-bold text-ink">Kvartaaliraportit</h2>
        <p class="mt-1 text-sm text-slate-500">Tulot, menot ja tulos neljännesvuosittain.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-light text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Kvartaali</th>
                    <th class="px-5 py-3 text-right font-semibold">Tulot</th>
                    <th class="px-5 py-3 text-right font-semibold">Menot</th>
                    <th class="px-5 py-3 text-right font-semibold">Voittomarginaali</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e8ef]">
            <?php foreach ($quarters as $q => $data): ?>
                <?php $quarterProfit = ($data['income'] ?? 0) - ($data['expense'] ?? 0); ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4 font-bold text-ink">Q<?php echo $q; ?></td>
                    <td class="px-5 py-4 text-right text-slate-600"><?php echo number_format($data['income'] ?? 0, 2); ?> €</td>
                    <td class="px-5 py-4 text-right text-slate-600"><?php echo number_format($data['expense'] ?? 0, 2); ?> €</td>
                    <td class="px-5 py-4 text-right font-semibold <?php echo $quarterProfit >= 0 ? 'text-emerald-600' : 'text-rose-600'; ?>"><?php echo number_format($quarterProfit, 2); ?> €</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require 'partials/footer.php'; ?>
