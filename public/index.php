<?php
declare(strict_types=1);

/**
 * FutStats — front controller.
 *   GET  index.php?p=<pagina>   → page_<pagina>()
 *   POST index.php?p=<acao>     → action_<acao>()   (com CSRF)
 */
require dirname(__DIR__) . '/src/bootstrap.php';

$page = preg_replace('/[^a-z_]/', '', (string) ($_GET['p'] ?? 'home')) ?: 'home';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $handler = 'action_' . $page;
    } else {
        $handler = 'page_' . $page;
    }

    if (!function_exists($handler)) {
        not_found();
    }

    $handler();
} catch (PDOException $ex) {
    http_response_code(500);
    error_log((string) $ex);
    render('404', ['message' => 'Erro no banco de dados: ' . $ex->getMessage()], 'Erro');
}
