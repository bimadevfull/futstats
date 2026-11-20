<?php
declare(strict_types=1);

/**
 * Conexão PDO (SQLite) em singleton. Cria o schema e os dados de demonstração
 * automaticamente quando o arquivo do banco ainda não existe.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = app_config();
        $path   = $config['db_path'];
        $isNew  = !file_exists($path);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');

        self::$pdo = $pdo;

        if ($isNew) {
            $pdo->exec((string) file_get_contents(BASE_PATH . '/database/schema.sql'));
            if (!empty($config['seed_demo'])) {
                require_once BASE_PATH . '/database/seed.php';
                seed_database($pdo);
            }
        }

        return $pdo;
    }
}
