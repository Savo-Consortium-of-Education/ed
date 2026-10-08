<?php
// Database configuration
define('DB_HOST', 'db');
define('DB_NAME', 'taloushallinto');
define('DB_USER', 'user');
define('DB_PASS', 'pass');

$userErrorMessage = 'Tietokantatoiminto epäonnistui. Yritä hetken kuluttua uudelleen.';

set_exception_handler(static function (Throwable $exception) use ($userErrorMessage): void {
    error_log(sprintf(
        'Unhandled %s: %s in %s:%d',
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $requestUri = htmlspecialchars(
        $_SERVER['REQUEST_URI'] ?? '/',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    $safeMessage = htmlspecialchars(
        $userErrorMessage,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );

    echo '<!DOCTYPE html><html lang="fi"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>Virhe | Pienyrityksen taloushallinto</title>'
        . '<link rel="stylesheet" href="assets/tailwind.css"></head>'
        . '<body><div class="page-shell">'
        . '<header class="mb-8 flex items-center gap-3 border-b border-border-subtle pb-6">'
        . '<img src="assets/logo.svg" alt="" class="h-14 w-14 rounded-2xl">'
        . '<span><span class="block text-lg font-bold tracking-tight text-ink">'
        . 'Pienyrityksen</span><span class="block text-sm font-medium text-slate-500">'
        . 'Taloushallinto</span></span></header>'
        . '<main><section class="surface mx-auto max-w-xl p-6 text-center sm:p-8">'
        . '<p class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand">'
        . 'Tietokantavirhe</p><h1 class="text-2xl font-bold text-ink">'
        . 'Tapahtui virhe</h1><p class="mt-3 text-slate-500">'
        . $safeMessage . '</p><a href="' . $requestUri
        . '" class="button-primary mt-6">Yritä uudelleen</a></section></main>'
        . '<footer class="mt-10 border-t border-border-subtle pt-5 text-sm text-slate-500">'
        . 'Pienyrityksen taloushallinto</footer></div></body></html>';
});

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>