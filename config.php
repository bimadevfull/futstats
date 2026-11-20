<?php
/**
 * Configuração do FutStats.
 * O banco SQLite é criado (e populado com dados de demonstração) automaticamente
 * na primeira execução.
 */
return [
    'app_name'  => 'FutStats',
    'db_path'   => __DIR__ . '/database/futstats.sqlite',
    'seed_demo' => true, // popula o banco com dados de exemplo na criação
    'timezone'  => 'America/Sao_Paulo',
];
