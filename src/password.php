<?php
require 'config.php';
require 'auth.php';

$currentUser = require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = 'Nykyinen salasana on virheellinen.';
    } elseif (strlen($new) < 8) {
        $error = 'Uuden salasanan tulee olla vähintään 8 merkkiä.';
    } elseif ($new !== $confirm) {
        $error = 'Salasanat eivät täsmää.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $currentUser['id']]);
        $message = 'Salasana vaihdettu.';
    }
}

$pageTitle = 'Vaihda salasana';
$activePage = 'password';
require 'partials/header.php';
?>
<div class="mx-auto max-w-md">
    <div class="mb-8">
        <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Oma tili</p>
        <h1 class="text-3xl font-bold tracking-tight text-ink">Vaihda salasana</h1>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="surface p-5 sm:p-8">
        <?php echo csrf_field(); ?>
        <div class="grid gap-5">
            <div>
                <label for="current_password" class="form-label">Nykyinen salasana</label>
                <input id="current_password" class="form-control" type="password" name="current_password" autocomplete="current-password" required>
            </div>
            <div>
                <label for="new_password" class="form-label">Uusi salasana</label>
                <input id="new_password" class="form-control" type="password" name="new_password" minlength="8" autocomplete="new-password" required>
            </div>
            <div>
                <label for="confirm_password" class="form-label">Uusi salasana uudelleen</label>
                <input id="confirm_password" class="form-control" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border-subtle pt-6">
            <button type="submit" class="button-primary">Vaihda salasana</button>
            <a href="index.php" class="button-secondary">Peruuta</a>
        </div>
    </form>
</div>
<?php require 'partials/footer.php'; ?>
