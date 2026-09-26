<?php
require 'config.php';
require 'auth.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        header('Location: index.php');
        exit;
    }

    $error = 'Virheellinen käyttäjätunnus tai salasana.';
}

$pageTitle = 'Kirjaudu sisään';
require 'partials/header.php';
?>
<div class="mx-auto max-w-md">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-ink">Kirjaudu sisään</h1>
        <p class="mt-2 text-slate-500">Kirjaudu käyttääksesi taloushallintoa.</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="surface p-5 sm:p-8">
        <?php echo csrf_field(); ?>
        <div class="grid gap-5">
            <div>
                <label for="username" class="form-label">Käyttäjätunnus</label>
                <input id="username" class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" autocomplete="username" required autofocus>
            </div>
            <div>
                <label for="password" class="form-label">Salasana</label>
                <input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required>
            </div>
        </div>
        <div class="mt-8 border-t border-border-subtle pt-6">
            <button type="submit" class="button-primary w-full">Kirjaudu</button>
        </div>
    </form>
</div>
<?php require 'partials/footer.php'; ?>
