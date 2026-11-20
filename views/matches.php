<?php
/** @var array|null $league @var int[] $rounds @var int $round @var string $status @var ?int $teamId @var array $grouped @var array $teams */
$base = array_filter(['status' => $status, 'team' => $teamId]);
$idx  = array_search($round, $rounds, true);
$prev = $idx !== false && $idx > 0 ? $rounds[$idx - 1] : null;
$next = $idx !== false && $idx < count($rounds) - 1 ? $rounds[$idx + 1] : null;
$total = array_sum(array_map('count', $grouped));
?>
<div class="page-head">
  <div>
    <h1>Partidas</h1>
    <p><?= e($league['name'] ?? '') ?> · <?= $total ?> jogo<?= $total === 1 ? '' : 's' ?> <?= $round ? "na {$round}ª rodada" : 'no filtro atual' ?></p>
  </div>
  <a class="btn btn-primary" href="<?= url('match_form') ?>"><?= icon('plus', 16) ?> Nova partida</a>
</div>

<section class="card">
  <div class="filters">
    <div class="round-nav">
      <a class="btn btn-sm btn-icon" href="<?= $prev ? e(url('matches', $base + ['round' => $prev])) : '#' ?>" <?= $prev ? '' : 'aria-disabled="true" style="opacity:.4;pointer-events:none"' ?>><?= icon('left', 16) ?></a>
      <span class="label"><?= $round ? "{$round}ª rodada" : 'Todas' ?></span>
      <a class="btn btn-sm btn-icon" href="<?= $next ? e(url('matches', $base + ['round' => $next])) : '#' ?>" <?= $next ? '' : 'aria-disabled="true" style="opacity:.4;pointer-events:none"' ?>><?= icon('right', 16) ?></a>
    </div>

    <div class="seg">
      <?php foreach (['' => 'Todos', 'live' => 'Ao vivo', 'finished' => 'Encerrados', 'scheduled' => 'Agendados'] as $key => $label): ?>
        <a class="<?= $status === $key ? 'on' : '' ?>" href="<?= e(url('matches', array_filter(['status' => $key, 'team' => $teamId]) + ['round' => $round])) ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <form method="get" action="index.php" class="row" style="margin-left:auto">
      <input type="hidden" name="p" value="matches">
      <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
      <select name="round" class="input input-sm" data-autosubmit style="width:auto">
        <option value="0">Todas as rodadas</option>
        <?php foreach ($rounds as $r): ?>
          <option value="<?= $r ?>" <?= $r === $round ? 'selected' : '' ?>><?= $r ?>ª rodada</option>
        <?php endforeach; ?>
      </select>
      <select name="team" class="input input-sm" data-autosubmit style="width:auto">
        <option value="">Todos os times</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (int) $t['id'] === $teamId ? 'selected' : '' ?>><?= e($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <div class="match-list">
    <?php foreach ($grouped as $day => $list): ?>
      <div class="day-head"><?= e(friendly_day($day)) ?> · <?= e(fmt_date($day, 'd/m/Y')) ?></div>
      <?php foreach ($list as $m) echo partial('match_row', ['m' => $m, 'showDate' => false]); ?>
    <?php endforeach; ?>
    <?php if (!$grouped): ?>
      <div class="card-empty">Nenhuma partida encontrada com esses filtros.</div>
    <?php endif; ?>
  </div>
</section>
