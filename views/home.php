<?php
/** @var array|null $league  @var array $counts  @var array $live  @var array $recent  @var array $upcoming  @var array $table  @var array $scorers */
$avgGoals = $counts['played'] ? number_format($counts['goals'] / $counts['played'], 2, ',', '') : '0';
?>
<div class="page-head">
  <div>
    <h1>Olá, torcedor 👋</h1>
    <p><?= e($league['name'] ?? 'Futebol') ?> <?= e($league['season'] ?? '') ?> — resultados, tabela e artilharia em tempo real.</p>
  </div>
  <div class="row">
    <a class="btn" href="<?= url('team_form') ?>"><?= icon('plus', 16) ?> Time</a>
    <a class="btn btn-primary" href="<?= url('match_form') ?>"><?= icon('plus', 16) ?> Nova partida</a>
  </div>
</div>

<section class="tiles">
  <div class="tile" style="--tile-c: var(--live)">
    <div class="tile-label">Ao vivo</div>
    <div class="tile-value num"><?= $counts['live'] ?></div>
    <div class="tile-sub">partidas agora</div>
  </div>
  <div class="tile">
    <div class="tile-label">Times</div>
    <div class="tile-value num"><?= $counts['teams'] ?></div>
    <div class="tile-sub">cadastrados</div>
  </div>
  <div class="tile" style="--tile-c: var(--accent-2)">
    <div class="tile-label">Jogadores</div>
    <div class="tile-value num"><?= $counts['players'] ?></div>
    <div class="tile-sub">nos elencos</div>
  </div>
  <div class="tile" style="--tile-c: var(--win)">
    <div class="tile-label">Partidas</div>
    <div class="tile-value num"><?= $counts['played'] ?></div>
    <div class="tile-sub">encerradas</div>
  </div>
  <div class="tile" style="--tile-c: var(--yellow)">
    <div class="tile-label">Gols</div>
    <div class="tile-value num"><?= $counts['goals'] ?></div>
    <div class="tile-sub"><?= $avgGoals ?> por jogo</div>
  </div>
</section>

<div class="grid-main">
  <div class="stack">
    <?php if ($live): ?>
      <section class="card">
        <div class="card-head">
          <h2><span class="live-dot"></span> Ao vivo agora</h2>
          <a class="link" href="<?= url('matches', ['status' => 'live', 'round' => 0]) ?>">Ver todos</a>
        </div>
        <div class="match-list">
          <?php foreach ($live as $m) echo partial('match_row', ['m' => $m]); ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="card">
      <div class="card-head">
        <h2><?= icon('flag', 16) ?> Últimos resultados</h2>
        <a class="link" href="<?= url('matches', ['status' => 'finished', 'round' => 0]) ?>">Ver todos</a>
      </div>
      <div class="match-list">
        <?php foreach ($recent as $m) echo partial('match_row', ['m' => $m]); ?>
        <?php if (!$recent): ?><div class="card-empty">Nenhuma partida encerrada ainda.</div><?php endif; ?>
      </div>
    </section>

    <section class="card">
      <div class="card-head">
        <h2><?= icon('calendar', 16) ?> Próximos jogos</h2>
        <a class="link" href="<?= url('matches', ['status' => 'scheduled', 'round' => 0]) ?>">Calendário</a>
      </div>
      <div class="match-list">
        <?php foreach ($upcoming as $m) echo partial('match_row', ['m' => $m]); ?>
        <?php if (!$upcoming): ?><div class="card-empty">Nenhum jogo agendado.</div><?php endif; ?>
      </div>
    </section>
  </div>

  <aside class="stack">
    <section class="card">
      <div class="card-head">
        <h2><?= icon('trophy', 16) ?> Classificação</h2>
        <a class="link" href="<?= url('standings') ?>">Tabela completa</a>
      </div>
      <div class="table-wrap">
        <table class="table compact">
          <thead><tr><th>#</th><th class="left">Time</th><th>J</th><th>SG</th><th>Pts</th></tr></thead>
          <tbody>
          <?php foreach ($table as $r): ?>
            <tr class="<?= zone($r['pos'], count($table)) ?>">
              <td><span class="pos"><?= $r['pos'] ?></span></td>
              <td class="left"><a class="team-cell" href="<?= url('team', ['id' => $r['id']]) ?>"><?= badge($r, 'sm') ?><span class="ellipsis"><?= e($r['name']) ?></span></a></td>
              <td class="num"><?= $r['p'] ?></td>
              <td class="num"><?= $r['gd'] > 0 ? '+' : '' ?><?= $r['gd'] ?></td>
              <td class="pts num"><?= $r['pts'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card">
      <div class="card-head">
        <h2><?= icon('target', 16) ?> Artilharia</h2>
        <a class="link" href="<?= url('players') ?>">Jogadores</a>
      </div>
      <?php foreach ($scorers as $i => $s): ?>
        <a class="scorer-row" href="<?= url('player', ['id' => $s['id']]) ?>">
          <span class="rank"><?= $i + 1 ?></span>
          <span class="avatar"><?= e(initials($s['name'])) ?></span>
          <span class="grow">
            <strong class="ellipsis" style="display:block"><?= e($s['name']) ?></strong>
            <span class="row small muted" style="gap:6px"><?= badge(['name' => $s['team_name']] + $s, 'xs') ?><?= e($s['team_name']) ?></span>
          </span>
          <span class="g num"><?= $s['goals'] ?></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$scorers): ?><div class="card-empty">Sem gols registrados.</div><?php endif; ?>
    </section>
  </aside>
</div>
