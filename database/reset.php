<?php
/**
 * Recria o banco do zero (com dados de demonstração, se ativados em config.php).
 * Uso:  php database/reset.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Execute pela linha de comando: php database/reset.php');
}

require dirname(__DIR__) . '/src/bootstrap.php';

$path = app_config()['db_path'];
foreach ([$path, "{$path}-wal", "{$path}-shm"] as $file) {
    if (file_exists($file)) unlink($file);
}

Database::get();

printf(
    "Banco recriado em %s\n  %d times · %d jogadores · %d partidas · %d eventos\n",
    $path,
    fetch_value('SELECT COUNT(*) FROM teams'),
    fetch_value('SELECT COUNT(*) FROM players'),
    fetch_value('SELECT COUNT(*) FROM matches'),
    fetch_value('SELECT COUNT(*) FROM match_events'),
);
