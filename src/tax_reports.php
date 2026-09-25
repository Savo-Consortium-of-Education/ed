<?php
require_once 'auth.php';
require_login();

$message = '';

if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $filename = ($type == 'vat') ? 'alv_ilmoitus.csv' : 'veroilmoitus.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, ['Päivämäärä', 'Tyyppi', 'Kategoria', 'Kuvaus', 'Summa', 'ALV-prosentti', 'ALV-summa']);

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
        ]);
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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Veroilmoitukset</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; padding: 5px 10px; background: #f0f0f0; }
        .export-btn { padding: 10px 15px; background: #2196F3; color: white; border: none; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <h1>Veroilmoitukset</h1>
    <nav>
        <a href="index.php">Koti</a>
        <a href="add_transaction.php">Lisää tapahtuma</a>
        <a href="reports.php">Raportit</a>
        <a href="tax_reports.php">Veroilmoitukset</a>
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="users.php">Käyttäjät</a>
        <?php endif; ?>
        <a href="logout.php">Kirjaudu ulos</a>
    </nav>
    <p>Kirjautunut: <?php echo h($_SESSION['username']); ?> (<?php echo h($_SESSION['role']); ?>)</p>

    <h2>ALV-ilmoitus</h2>
    <p>ALV maksettava: <?php echo number_format($vat_payable, 2); ?> €</p>
    <p>ALV vähennettävä: <?php echo number_format($vat_deductible, 2); ?> €</p>
    <p>ALV-saldo: <?php echo number_format($vat_balance, 2); ?> €</p>
    <button class="export-btn" onclick="window.location.href='?export=vat'">Vie ALV-ilmoitus CSV:ään</button>

    <h2>Veroilmoitus</h2>
    <p>Kokonais tulot: <?php echo number_format($total_income, 2); ?> €</p>
    <p>Kokonais menot: <?php echo number_format($total_expense, 2); ?> €</p>
    <p>Verotettava tulo: <?php echo number_format($total_income - $total_expense, 2); ?> €</p>
    <button class="export-btn" onclick="window.location.href='?export=tax'">Vie veroilmoitus CSV:ään</button>
</body>
</html>