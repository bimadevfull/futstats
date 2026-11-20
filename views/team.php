<?php
/** @var array $t @var array|null $row @var array $squad @var array $recent @var array $upcoming */
$c1 = safe_color($t['color_primary']);
$c2 = safe_color($t['color_secondary'], '#ffffff');
$playersCount = array_sum(array_map('count', $squad));
$allPlayers = array_merge(...array_values($squad ?: [[]]));
$ratings = array_filter(array_column($allPlayers, 'rating'), fn ($r) => $r !== null);
$avgRating = $ratings ? array_sum($ratings) / count($ratings) : null;
$ages = array_filter(array_map(fn ($p) => age($p['birth_date']), $allPlayers));
$avgAge = $ages ? array_sum($ages) / count($ages) : null;
?>
<nav class="breadcrumb"><a href="<?= url('teams') ?>">Times</a> / <span><?= e($t['name']) ?></span></nav>

<section class="hero" style="--h1: <?= $c1 ?>; --h2: <?= $c2 ?>">
  <div class="hero-inner">
    <?= badge($t, 'xl') ?>
    <div>
      <h1><?= e($t['name']) ?></h1>
      <div class="meta">
        <?php if ($t['league_name']): ?><span class="tag"><?= icon('trophy', 13) ?> <?= e($t['league_name']) ?></span><?php endif; ?>
        <?php if ($t['city']): ?><span class="tag"><?= icon('pin', 13) ?> <?= e($t['city']) ?></span><?php endif; ?>
        <?php if ($t['stadium']): ?><span class="tag"><?= icon('home', 13) ?> <?= e($t['stadium']) ?></span><?php endif; ?>
        <?php if ($t['founded']): ?><span class="tag"><?= icon('calendar', 13) ?> Fundado em <?= (int) $t['founded'] ?></span><?php endif; ?>
        <?php if ($t['coach']): ?><span class="tag"><?= icon('whistle', 13) ?> <?= e($t['coach']) ?></span><?php endif; ?>
      </div>
    </div>
    <div class="hero-actions">
      <a class="btn btn-sm" href="<?= url('team_form', ['id' => $t['id']]) ?>"><?= icon('edit', 14) ?> Editar</a>
      <?= delete_button('team_delete', (int) $t['id'], "Excluir {$t['name']}? Jogadores e partidas do time também serão apagados.") ?>
    </div>
  </div>
</section>

<section class="tiles">
  <div class="tile"><div class="tile-label">Posição</div><div class="tile-value"><?= $row ? $row['pos'] . 'º' : '—' ?></div><div class="tile-sub"><?= $row ? $row['pts'] . ' pontos' : 'sem jogos' ?></div></div>
  <div class="tile" style="--tile-c: var(--win)"><div class="tile-label">Campanha</div><div class="tile-value num"><?= $row ? "{$row['w']}-{$row['d']}-{$row['l']}" : '—' ?></div><div class="tile-sub">V-E-D · <?= $row['pct'] ?? 0 ?>% aprov.</div></div>
  <div class="tile" style="--tile-c: var(--yellow)"><div class="tile-label">Gols</div><div class="tile-value num"><?= $row ? "{$row['gf']}:{$row['ga']}" : '—' ?></div><div class="tile-sub">pró : contra</div></div>
  <div class="tile" style="--tile-c: var(--accent-2)"><div class="tile-label">Elenco</div><div class="tile-value num"><?= $playersCount ?></div><div class="tile-sub"><?= $avgAge ? number_format($avgAge, 1, ',', '') . ' anos em média' : 'jogadores' ?></div></div>
  <div class="tile" style="--tile-c: var(--live)"><div class="tile-label">Nota média</div><div class="tile-value num"><?= $avgRating ? number_format($avgRating, 2, ',', '') : '—' ?></div><div class="tile-sub">do elenco</div></div>
</section>

<div class="grid-main">
  <section class="card">
    <div class="card-head">
      <h2><?= icon('users', 16) ?> Elenco</h2>
      <a class="btn btn-sm" href="<?= url('player_form', ['team' => $t['id']]) ?>"><?= icon('plus', 14) ?> Jogador</a>
    </div>
    <?php foreach (POSITIONS_PLURAL as $pos => $label): if (empty($squad[$pos])) continue; ?>
      <div class="squad-group">
        <div class="squad-title"><?= $label ?> · <?= count($squad[$pos]) ?></div>
        <?php foreach ($squad[$pos] as $p): $a = age($p['birth_date']); ?>
          <a class="player-row" href="<?= url('player', ['id' => $p['id']]) ?>">
            <span class="num-shirt"><?= e($p['number']) ?></span>
            <span class="avatar"><?= e(initials($p['name'])) ?></span>
            <span class="info">
              <strong><?= e($p['name']) ?></strong>
              <small><?= e($p['nationality'] ?: '—') ?><?= $a ? " · {$a} anos" : '' ?><?= $p['height'] ? ' · ' . number_format($p['height'] / 100, 2, ',', '') . ' m' : '' ?></small>
            </span>
            <?php if ((int) $p['goals'] > 0): ?><span class="goals"><?= icon('ball', 13) ?> <?= (int) $p['goals'] ?></span><?php endif; ?>
            <span class="rating <?= rating_class($p['rating'] !== null ? (float) $p['rating'] : null) ?>"><?= $p['rating'] !== null ? number_format((float) $p['rating'], 1) : '–' ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$squad): ?><div class="card-empty">Nenhum jogador cadastrado. <a class="link" style="color:var(--accent)" href="<?= url('player_form', ['team' => $t['id']]) ?>">Adicionar agora</a></div><?php endif; ?>
  </section>

  <aside class="stack">
    <?php if ($row && $row['form']): ?>
      <section class="card">
        <div class="card-head"><h2><?= icon('chart', 16) ?> Forma recente</h2></div>
        <div class="card-body row-between">
          <span class="form"><?php foreach ($row['form'] as $f): ?><b class="<?= $f ?>"><?= $f ?></b><?php endforeach; ?></span>
          <span class="muted small">saldo <?= $row['gd'] > 0 ? '+' : '' ?><?= $row['gd'] ?></span>
        </div>
      </section>
    <?php endif; ?>

    <section class="card">
      <div class="card-head">
        <h2><?= icon('calendar', 16) ?> Próximos jogos</h2>
        <a class="link" href="<?= url('matches', ['team' => $t['id']]) ?>">Todos</a>
      </div>
      <div class="match-list">
        <?php foreach ($upcoming as $m) echo partial('match_row', ['m' => $m]); ?>
        <?php if (!$upcoming): ?><div class="card-empty">Nenhum jogo agendado.</div><?php endif; ?>
      </div>
    </section>

    <section class="card">
      <div class="card-head"><h2><?= icon('flag', 16) ?> Últimos resultados</h2></div>
      <div class="match-list">
        <?php foreach ($recent as $m) echo partial('match_row', ['m' => $m]); ?>
        <?php if (!$recent): ?><div class="card-empty">Nenhum jogo disputado.</div><?php endif; ?>
      </div>
    </section>
  </aside>
</div>
