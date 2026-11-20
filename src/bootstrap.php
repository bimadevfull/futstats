<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

if (session_status() !== PHP_SESSION_ACTIVE && PHP_SAPI !== 'cli') {
    session_start();
}

require BASE_PATH . '/src/helpers.php';
date_default_timezone_set(app_config()['timezone'] ?? 'America/Sao_Paulo');

require BASE_PATH . '/src/Database.php';
require BASE_PATH . '/src/repository.php';
require BASE_PATH . '/src/controllers.php';
require BASE_PATH . '/src/actions.php';
