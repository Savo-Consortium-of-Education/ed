<?php
require_once 'auth.php';
require_admin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($username === '' || $password === '') {
        $error = 'Käyttäjätunnus ja salasana ovat pakollisia.';
    } elseif (!in_array($role, ['admin', 'user'], true)) {
        $error = 'Virheellinen rooli.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $error = 'Käyttäjätunnus on jo käytössä.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)');
            $stmt->execute([$username, $password_hash, $role]);
            $message = 'Käyttäjä lisätty onnistuneesti.';
        }
    }
}

$users = $pdo->query('SELECT id, username, role, is_active, created_at FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Käyttäjien hallinta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; padding: 5px 10px; background: #f0f0f0; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { max-width: 420px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 5px; }
        button { margin-top: 15px; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .message { color: green; }
        .error { color: #b00020; }
    </style>
</head>
<body>
    <h1>Käyttäjien hallinta</h1>
    <nav>
        <a href="index.php">Koti</a>
        <a href="add_transaction.php">Lisää tapahtuma</a>
        <a href="reports.php">Raportit</a>
        <a href="tax_reports.php">Veroilmoitukset</a>
        <a href="users.php">Käyttäjät</a>
        <a href="logout.php">Kirjaudu ulos</a>
    </nav>
    <p>Kirjautunut: <?php echo h($_SESSION['username']); ?> (<?php echo h($_SESSION['role']); ?>)</p>

    <?php if ($message): ?>
        <p class="message"><?php echo h($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo h($error); ?></p>
    <?php endif; ?>

    <h2>Lisää käyttäjä</h2>
    <form method="post">
        <label>Käyttäjätunnus:</label>
        <input type="text" name="username" required>

        <label>Salasana:</label>
        <input type="password" name="password" required>

        <label>Rooli:</label>
        <select name="role">
            <option value="user">Käyttäjä</option>
            <option value="admin">Ylläpitäjä</option>
        </select>

        <button type="submit">Lisää käyttäjä</button>
    </form>

    <h2>Käyttäjät</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Käyttäjätunnus</th>
            <th>Rooli</th>
            <th>Tila</th>
            <th>Luotu</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo (int) $user['id']; ?></td>
            <td><?php echo h($user['username']); ?></td>
            <td><?php echo h($user['role']); ?></td>
            <td><?php echo ((int) $user['is_active'] === 1) ? 'Aktiivinen' : 'Ei aktiivinen'; ?></td>
            <td><?php echo h($user['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
