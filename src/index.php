<?php
require 'config.php';

$summary = $pdo->query(
    "SELECT
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense,
        SUM(CASE WHEN type = 'income' THEN vat_amount ELSE 0 END)
            - SUM(CASE WHEN type = 'expense' THEN vat_amount ELSE 0 END) AS vat_balance
     FROM transactions"
)->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM transactions ORDER BY date DESC LIMIT 10");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Koti';
$activePage = 'home';
require 'partials/header.php';
?>
<section class="mb-8">
    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Yleiskatsaus</p>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-ink sm:text-4xl">Tervetuloa takaisin</h1>
            <p class="mt-2 max-w-2xl text-slate-500">Seuraa yrityksesi taloutta yhdestä selkeästä näkymästä.</p>
        </div>
        <a href="add_transaction.php" class="button-primary">Lisää tapahtuma</a>
    </div>
</section>

<section class="mb-8 grid gap-4 md:grid-cols-3">
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">Kokonaistulot</p>
        <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($summary['total_income'] ?? 0, 2); ?> €</p>
    </article>
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">Kokonaismenot</p>
        <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($summary['total_expense'] ?? 0, 2); ?> €</p>
    </article>
    <article class="metric-card">
        <p class="text-sm font-semibold text-slate-500">ALV-saldo</p>
        <p class="mt-3 text-2xl font-bold text-ink"><?php echo number_format($summary['vat_balance'] ?? 0, 2); ?> €</p>
    </article>
</section>

<section class="surface overflow-hidden">
    <div class="flex flex-col gap-2 border-b border-[#d7dbe8] px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-ink">Viimeisimmät tapahtumat</h2>
            <p class="mt-1 text-sm text-slate-500">Kymmenen viimeksi kirjattua tapahtumaa.</p>
        </div>
        <a href="add_transaction.php" class="text-sm font-semibold text-brand hover:text-blue-700">Lisää uusi</a>
    </div>
    <?php if (!$transactions): ?>
        <p class="px-5 py-10 text-center text-slate-500">Tapahtumia ei ole vielä lisätty.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-brand-light text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Päivämäärä</th>
                        <th class="px-5 py-3 font-semibold">Tyyppi</th>
                        <th class="px-5 py-3 font-semibold">Kategoria</th>
                        <th class="px-5 py-3 font-semibold">Kuvaus</th>
                        <th class="px-5 py-3 text-right font-semibold">Summa</th>
                        <th class="px-5 py-3 text-right font-semibold">ALV</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e8ef]">
                <?php foreach ($transactions as $row): ?>
                    <tr class="transition hover:bg-slate-50">
                        <td class="whitespace-nowrap px-5 py-4 text-slate-600"><?php echo htmlspecialchars($row['date']); ?></td>
                        <td class="px-5 py-4 font-semibold <?php echo $row['type'] === 'income' ? 'text-emerald-600' : 'text-slate-600'; ?>">
                            <?php echo $row['type'] === 'income' ? 'Tulo' : 'Meno'; ?>
                        </td>
                        <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['category']))); ?></td>
                        <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars($row['description']); ?></td>
                        <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-ink"><?php echo number_format($row['amount'], 2); ?> €</td>
                        <td class="whitespace-nowrap px-5 py-4 text-right text-slate-600"><?php echo number_format($row['vat_amount'], 2); ?> €</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require 'partials/footer.php'; ?>
