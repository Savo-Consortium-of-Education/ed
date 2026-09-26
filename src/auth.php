<?php
// Authentication, roles and CSRF protection. Include after config.php.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const ROLES = [
    'admin' => 'Ylläpitäjä',
    'accountant' => 'Kirjanpitäjä',
    'viewer' => 'Lukija',
];

// Roles allowed to add transactions
const WRITE_ROLES = ['admin', 'accountant'];

function current_user(): ?array
{
    global $pdo;
    static $user = false;

    if ($user === false) {
        $user = null;
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if (!$user) {
                // User was deleted while logged in
                unset($_SESSION['user_id']);
            }
        }
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_role(array $roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        $pageTitle = 'Ei oikeuksia';
        require __DIR__ . '/partials/header.php';
        echo '<div class="surface mx-auto max-w-xl p-8 text-center">'
            . '<h1 class="text-2xl font-bold text-ink">Ei oikeuksia</h1>'
            . '<p class="mt-2 text-slate-500">Sinulla ei ole oikeuksia tähän toimintoon.</p>'
            . '<a href="index.php" class="button-primary mt-6">Takaisin etusivulle</a>'
            . '</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
    return $user;
}

function can_write(): bool
{
    $user = current_user();
    return $user && in_array($user['role'], WRITE_ROLES, true);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        die('Virheellinen lomakkeen tunniste. Lataa sivu uudelleen ja yritä uudelleen.');
    }
}
