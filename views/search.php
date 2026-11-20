<?php /** @var string $q @var array $results */ ?>
<div class="page-head">
  <div>
    <h1>Busca</h1>
    <p><?= $q !== '' ? 'Resultados para “' . e($q) . '”' : 'Digite ao menos 2 letras na barra de busca.' ?></p>
  </div>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card-head"><h2><?= icon('shield', 16) ?> Times · <?= count($results['teams']) ?></h2></div>
    <?php foreach ($results['teams'] as $t): ?>
      <a class="player-row" href="<?= url('team', ['id' => $t['id']]) ?>">
        <?= badge($t, 'md') ?>
        <span class="info"><strong><?= e($t['name']) ?></strong><small><?= e($t['city'] ?: '—') ?></small></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$results['teams']): ?><div class="card-empty">Nenhum time encontrado.</div><?php endif; ?>
  </section>

  <section class="card">
    <div class="card-head"><h2><?= icon('users', 16) ?> Jogadores · <?= count($results['players']) ?></h2></div>
    <?php foreach (array_slice($results['players'], 0, 30) as $p): ?>
      <a class="player-row" href="<?= url('player', ['id' => $p['id']]) ?>">
        <span class="avatar"><?= e(initials($p['name'])) ?></span>
        <span class="info"><strong><?= e($p['name']) ?></strong><small><?= e($p['team_name']) ?> · <?= e(position_label($p['position'])) ?></small></span>
        <span class="rating <?= rating_class($p['rating'] !== null ? (float) $p['rating'] : null) ?>"><?= $p['rating'] !== null ? number_format((float) $p['rating'], 1) : '–' ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$results['players']): ?><div class="card-empty">Nenhum jogador encontrado.</div><?php endif; ?>
  </section>
</div>
