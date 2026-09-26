<?php
$pageTitle = $pageTitle ?? 'Pienyrityksen taloushallinto';
$activePage = $activePage ?? '';
$navUser = function_exists('current_user') ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Pienyrityksen taloushallinto</title>
    <link rel="stylesheet" href="assets/tailwind.css">
</head>
<body>
    <div class="page-shell">
        <header class="mb-8 flex flex-col gap-6 border-b border-border-subtle pb-6 lg:flex-row lg:items-center lg:justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <img src="assets/logo.svg" alt="" class="h-14 w-14 rounded-2xl">
                <span>
                    <span class="block text-lg font-bold tracking-tight text-ink">Pienyrityksen</span>
                    <span class="block text-sm font-medium text-slate-500">Taloushallinto</span>
                </span>
            </a>
            <?php if ($navUser): ?>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <nav aria-label="Päänavigaatio" class="flex flex-wrap gap-2">
                <?php
                $links = ['home' => ['index.php', 'Koti']];
                if (can_write()) {
                    $links['add'] = ['add_transaction.php', 'Lisää tapahtuma'];
                }
                $links['reports'] = ['reports.php', 'Raportit'];
                $links['tax'] = ['tax_reports.php', 'Veroilmoitukset'];
                if ($navUser['role'] === 'admin') {
                    $links['users'] = ['users.php', 'Käyttäjät'];
                }
                foreach ($links as $key => [$href, $label]):
                    $classes = $activePage === $key
                        ? 'bg-brand text-white shadow-sm'
                        : 'text-ink hover:bg-brand-light';
                ?>
                    <a href="<?php echo $href; ?>" class="rounded-xl px-3 py-2 text-sm font-semibold transition <?php echo $classes; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="flex items-center gap-3 border-border-subtle text-sm lg:border-l lg:pl-4">
                <a href="password.php" class="text-slate-500 hover:text-ink" title="Vaihda salasana">
                    <span class="font-semibold text-ink"><?php echo htmlspecialchars($navUser['username']); ?></span>
                    (<?php echo htmlspecialchars(ROLES[$navUser['role']]); ?>)
                </a>
                <form method="post" action="logout.php">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="button-secondary py-1.5">Kirjaudu ulos</button>
                </form>
            </div>
            </div>
            <?php endif; ?>
        </header>
        <main>
