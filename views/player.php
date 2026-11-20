<?php
/** @var array $p @var array $stats @var array $events */
$team = ['id' => $p['team_id'], 'name' => $p['team_name'], 'short_name' => $p['short_name'], 'color_primary' => $p['color_primary'], 'color_secondary' => $p['color_secondary']];
$a = age($p['birth_date']);
$rating = $p['rating'] !== null ? (float) $p['rating'] : null;
?>
<nav class="breadcrumb">
  <a href="<?= url('players') ?>">Jogadores</a> /
  <a href="<?= url('team', ['id' => $p['team_id']]) ?>"><?= e($p['team_name']) ?></a> /
  <span><?= e($p['name']) ?></span>
</nav>

<section class="hero" style="--h1: <?= safe_color($p['color_primary']) ?>; --h2: <?= safe_color($p['color_secondary'], '#ffffff') ?>">
  <div class="hero-inner">
    <span class="avatar avatar-lg"><?= e(initials($p['name'])) ?></span>
    <div>
      <div class="row small muted" style="margin-bottom:6px">
        <span class="pos-tag pos-<?= e($p['position']) ?>"><?= e(position_label($p['position'])) ?></span>
        <?php if ($p['number']): ?><span>Camisa <?= (int) $p['number'] ?></span><?php endif; ?>
      </div>
      <h1><?= e($p['name']) ?></h1>
      <div class="meta">
        <a class="tag" href="<?= url('team', ['id' => $p['team_id']]) ?>"><?= badge($team, 'xs') ?> <?= e($p['team_name']) ?></a>
        <?php if ($p['nationality']): ?><span class="tag"><?= icon('flag', 13) ?> <?= e($p['nationality']) ?></span><?php endif; ?>
      </div>
    </div>
    <div class="hero-actions">
      <a class="btn btn-sm" href="<?= url('player_form', ['id' => $p['id']]) ?>"><?= icon('edit', 14) ?> Editar</a>
      <?= delete_button('player_delete', (int) $p['id'], "Excluir {$p['name']}?") ?>
    </div>
  </div>
</section>

<section class="tiles">
  <div class="tile" style="--tile-c: var(--live)"><div class="tile-label">Nota</div><div class="tile-value"><span class="rating <?= rating_class($rating) ?>" style="font-size:22px;padding:2px 10px"><?= $rating !== null ? number_format($rating, 1) : '–' ?></span></div><div class="tile-sub">média na temporada</div></div>
  <div class="tile" style="--tile-c: var(--yellow)"><div class="tile-label">Gols</div><div class="tile-value num"><?= $stats['goals'] ?? 0 ?></div><div class="tile-sub"><?= $stats['penalties'] ?? 0 ?> de pênalti</div></div>
  <div class="tile" style="--tile-c: var(--win)"><div class="tile-label">Assistências</div><div class="tile-value num"><?= $stats['assists'] ?? 0 ?></div><div class="tile-sub">participações: <?= ($stats['goals'] ?? 0) + ($stats['assists'] ?? 0) ?></div></div>
  <div class="tile" style="--tile-c: var(--yellow)"><div class="tile-label">Amarelos</div><div class="tile-value num"><?= $stats['yellows'] ?? 0 ?></div><div class="tile-sub">cartões</div></div>
  <div class="tile" style="--tile-c: var(--loss)"><div class="tile-label">Vermelhos</div><div class="tile-value num"><?= $stats['reds'] ?? 0 ?></div><div class="tile-sub">expulsões</div></div>
</section>

<div class="grid-main">
  <section class="card">
    <div class="card-head"><h2><?= icon('ball', 16) ?> Participações em partidas</h2></div>
    <?php if ($events): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th class="left">Partida</th><th>Placar</th><th>Min</th><th class="left">Evento</th></tr></thead>
          <tbody>
          <?php foreach ($events as $ev):
              $isAssist = (int) $ev['ev_assist'] === (int) $p['id'] && (int) $ev['ev_player'] !== (int) $p['id']; ?>
            <tr>
              <td class="left">
                <a class="row hover-accent" href="<?= url('match', ['id' => $ev['id']]) ?>">
                  <span class="muted small nowrap"><?= e(fmt_date($ev['match_date'], 'd/m')) ?></span>
                  <?= badge(side($ev, 'home'), 'xs') ?> <?= e($ev['home_short']) ?> <span class="muted">x</span> <?= e($ev['away_short']) ?> <?= badge(side($ev, 'away'), 'xs') ?>
                </a>
              </td>
              <td class="num"><strong><?= (int) $ev['home_score'] ?> - <?= (int) $ev['away_score'] ?></strong></td>
              <td class="num"><?= (int) $ev['ev_minute'] ?>'</td>
              <td class="left">
                <span class="row">
                  <?php if ($isAssist): ?>
                    <span class="ev-icon" style="color:var(--win)"><?= icon('target', 16) ?></span> Assistência
                  <?php else: ?>
                    <?= event_icon($ev['ev_type']) ?> <?= e(EVENT_TYPES[$ev['ev_type']]) ?>
                  <?php endif; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="card-empty">Nenhum gol, assistência ou cartão registrado.</div>
    <?php endif; ?>
  </section>

  <aside class="card">
    <div class="card-head"><h2><?= icon('users', 16) ?> Dados pessoais</h2></div>
    <dl class="kv">
      <dt>Idade</dt><dd><?= $a ? "{$a} anos" : '—' ?></dd>
      <dt>Nascimento</dt><dd><?= e(fmt_date($p['birth_date'], 'd/m/Y')) ?></dd>
      <dt>Nacionalidade</dt><dd><?= e($p['nationality'] ?: '—') ?></dd>
      <dt>Altura</dt><dd><?= $p['height'] ? number_format($p['height'] / 100, 2, ',', '') . ' m' : '—' ?></dd>
      <dt>Pé preferido</dt><dd><?= e($p['foot'] ?: '—') ?></dd>
      <dt>Posição</dt><dd><?= e(position_label($p['position'])) ?></dd>
      <dt>Camisa</dt><dd><?= e($p['number'] ?: '—') ?></dd>
    </dl>
  </aside>
</div>
