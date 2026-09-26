<?php
require 'config.php';
require 'auth.php';

$currentUser = require_role(['admin']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    $password = (string)($_POST['password'] ?? '');

    if ($action === 'create') {
        $username = trim((string)($_POST['username'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            $error = 'Käyttäjätunnuksen tulee olla 3–50 merkkiä (kirjaimet, numerot, . _ -).';
        } elseif (strlen($password) < 8) {
            $error = 'Salasanan tulee olla vähintään 8 merkkiä.';
        } elseif (!isset(ROLES[$role])) {
            $error = 'Virheellinen rooli.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Käyttäjätunnus on jo käytössä.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                $message = 'Käyttäjä lisätty.';
            }
        }
    } elseif ($action === 'role') {
        if ($id === (int)$currentUser['id']) {
            $error = 'Et voi muuttaa omaa rooliasi.';
        } elseif (!isset(ROLES[$role])) {
            $error = 'Virheellinen rooli.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);
            $message = 'Rooli päivitetty.';
        }
    } elseif ($action === 'password') {
        if (strlen($password) < 8) {
            $error = 'Salasanan tulee olla vähintään 8 merkkiä.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            $message = 'Salasana vaihdettu.';
        }
    } elseif ($action === 'delete') {
        if ($id === (int)$currentUser['id']) {
            $error = 'Et voi poistaa omaa käyttäjääsi.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Käyttäjä poistettu.';
        }
    }
}

$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Käyttäjät';
$activePage = 'users';
require 'partials/header.php';
?>
<div class="mb-8">
    <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">Ylläpito</p>
    <h1 class="text-3xl font-bold tracking-tight text-ink">Käyttäjät</h1>
    <p class="mt-2 text-slate-500">Hallitse sovelluksen käyttäjiä ja heidän roolejaan.</p>
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

<section class="surface mb-8 overflow-hidden">
    <div class="border-b border-[#d7dbe8] px-5 py-5">
        <h2 class="text-xl font-bold text-ink">Käyttäjälista</h2>
        <p class="mt-1 text-sm text-slate-500">Ylläpitäjä: kaikki oikeudet. Kirjanpitäjä: katselu ja tapahtumien lisäys. Lukija: vain katselu.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-light text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Käyttäjätunnus</th>
                    <th class="px-5 py-3 font-semibold">Rooli</th>
                    <th class="px-5 py-3 font-semibold">Uusi salasana</th>
                    <th class="px-5 py-3 font-semibold">Luotu</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e8ef]">
            <?php foreach ($users as $u): ?>
                <?php $isSelf = (int)$u['id'] === (int)$currentUser['id']; ?>
                <tr>
                    <td class="px-5 py-4 font-semibold text-ink">
                        <?php echo htmlspecialchars($u['username']); ?>
                        <?php if ($isSelf): ?><span class="ml-1 text-xs font-normal text-slate-500">(sinä)</span><?php endif; ?>
                    </td>
                    <td class="px-5 py-4">
                        <?php if ($isSelf): ?>
                            <?php echo htmlspecialchars(ROLES[$u['role']]); ?>
                        <?php else: ?>
                            <form method="post" class="flex gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="role">
                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                <select name="role" class="form-control py-1.5" aria-label="Rooli">
                                    <?php foreach (ROLES as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $u['role'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="button-secondary py-1.5">Tallenna</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4">
                        <form method="post" class="flex gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="password">
                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                            <input type="password" name="password" class="form-control py-1.5" minlength="8" autocomplete="new-password" aria-label="Uusi salasana" required>
                            <button type="submit" class="button-secondary py-1.5">Vaihda</button>
                        </form>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600"><?php echo htmlspecialchars($u['created_at']); ?></td>
                    <td class="px-5 py-4 text-right">
                        <?php if (!$isSelf): ?>
                            <form method="post" onsubmit="return confirm('Poistetaanko käyttäjä?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-800">Poista</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<form method="post" class="surface p-5 sm:p-8">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="create">
    <h2 class="mb-5 text-xl font-bold text-ink">Lisää käyttäjä</h2>
    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="new_username" class="form-label">Käyttäjätunnus</label>
            <input id="new_username" class="form-control" type="text" name="username" pattern="[A-Za-z0-9._\-]{3,50}" autocomplete="off" required>
        </div>
        <div>
            <label for="new_password" class="form-label">Salasana</label>
            <input id="new_password" class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" required>
        </div>
        <div>
            <label for="new_role" class="form-label">Rooli</label>
            <select id="new_role" class="form-control" name="role" required>
                <?php foreach (ROLES as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $value === 'viewer' ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mt-8 border-t border-border-subtle pt-6">
        <button type="submit" class="button-primary">Lisää käyttäjä</button>
    </div>
</form>
<?php require 'partials/footer.php'; ?>
