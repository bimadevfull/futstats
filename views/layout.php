<?php
$appName  = app_config()['app_name'];
$flashMsg = flash();
$liveNow  = (int) fetch_value("SELECT COUNT(*) FROM matches WHERE status = 'live'");
?>
<!doctype html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ? "{$title} · {$appName}" : $appName) ?></title>
  <meta name="description" content="Placar, classificação, times e jogadores — gerenciamento de dados de futebol.">
  <script>try { var t = localStorage.getItem('futstats-theme'); if (t) document.documentElement.dataset.theme = t; } catch (e) {}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <a class="brand" href="<?= url() ?>">
      <span class="brand-logo"><?= icon('ball', 20) ?></span>
      <span>Fut<b>Stats</b></span>
    </a>

    <nav class="nav" id="mainNav">
      <a class="<?= active('home') ?>" href="<?= url() ?>"><?= icon('home', 16) ?> Início</a>
      <a class="<?= active('matches') ?>" href="<?= url('matches') ?>"><?= icon('calendar', 16) ?> Partidas
        <?php if ($liveNow): ?><span class="live-count"><?= $liveNow ?></span><?php endif; ?></a>
      <a class="<?= active('standings') ?>" href="<?= url('standings') ?>"><?= icon('trophy', 16) ?> Classificação</a>
      <a class="<?= active('teams') ?>" href="<?= url('teams') ?>"><?= icon('shield', 16) ?> Times</a>
      <a class="<?= active('players') ?>" href="<?= url('players') ?>"><?= icon('users', 16) ?> Jogadores</a>
    </nav>

    <form class="search" action="index.php" method="get" role="search">
      <input type="hidden" name="p" value="search">
      <?= icon('search', 16) ?>
      <input type="search" name="q" placeholder="Buscar times e jogadores…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar">
    </form>

    <button class="icon-btn" id="themeToggle" type="button" title="Alternar tema" aria-label="Alternar tema">
      <span class="icon-sun"><?= icon('sun', 18) ?></span><span class="icon-moon"><?= icon('moon', 18) ?></span>
    </button>
    <button class="icon-btn menu-btn" id="menuToggle" type="button" aria-label="Menu"><?= icon('menu', 18) ?></button>
  </div>
</header>

<main class="container page">
  <?php if ($flashMsg): ?>
    <div class="flash flash-<?= e($flashMsg['type']) ?>" role="status">
      <?= icon($flashMsg['type'] === 'error' ? 'alert' : 'check', 18) ?> <?= e($flashMsg['msg']) ?>
    </div>
  <?php endif; ?>

  <?= $content ?>
</main>

<footer class="footer">
  <div class="container">
    <span><strong><?= e($appName) ?></strong> — sistema de gerenciamento de dados de futebol em PHP</span>
    <span>Dados de demonstração fictícios · <?= date('Y') ?></span>
  </div>
</footer>

<script src="assets/js/app.js"></script>
</body>
</html>
