<?php
/** @var array|null $league @var array $leagues @var array $table @var array $scorers */
$total = count($table);
?>
<div class="page-head">
  <div>
    <h1>Classificação</h1>
    <p><?= e($league['name'] ?? '') ?> <?= e($league['season'] ?? '') ?> · critérios: pontos, vitórias, saldo, gols pró</p>
  </div>
  <?php if (count($leagues) > 1): ?>
    <form method="get" action="index.php">
      <input type="hidden" name="p" value="standings">
      <select name="league" class="input" data-autosubmit>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= $l['id'] ?>" <?= (int) $l['id'] === (int) ($league['id'] ?? 0) ? 'selected' : '' ?>><?= e($l['name']) ?> <?= e($l['season']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  <?php endif; ?>
</div>

<div class="grid-main">
  <section class="card">
    <div class="table-wrap">
      <table class="table" data-sortable>
        <thead>
          <tr>
            <th data-sort="num">#</th>
            <th class="left" data-sort="text">Time</th>
            <th data-sort="num" title="Pontos">Pts</th>
            <th data-sort="num" title="Jogos">J</th>
            <th data-sort="num" title="Vitórias">V</th>
            <th data-sort="num" title="Empates">E</th>
            <th data-sort="num" title="Derrotas">D</th>
            <th data-sort="num" title="Gols pró" class="hide-sm">GP</th>
            <th data-sort="num" title="Gols contra" class="hide-sm">GC</th>
            <th data-sort="num" title="Saldo de gols">SG</th>
            <th data-sort="num" title="Aproveitamento" class="hide-sm">%</th>
            <th class="hide-sm">Últimos 5</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($table as $r): ?>
          <tr class="<?= zone($r['pos'], $total) ?>">
            <td data-value="<?= $r['pos'] ?>"><span class="pos"><?= $r['pos'] ?></span></td>
            <td class="left" data-value="<?= e($r['name']) ?>">
              <a class="team-cell" href="<?= url('team', ['id' => $r['id']]) ?>"><?= badge($r, 'sm') ?> <?= e($r['name']) ?></a>
            </td>
            <td class="pts num"><?= $r['pts'] ?></td>
            <td class="num"><?= $r['p'] ?></td>
            <td class="num"><?= $r['w'] ?></td>
            <td class="num"><?= $r['d'] ?></td>
            <td class="num"><?= $r['l'] ?></td>
            <td class="num hide-sm"><?= $r['gf'] ?></td>
            <td class="num hide-sm"><?= $r['ga'] ?></td>
            <td class="num" data-value="<?= $r['gd'] ?>"><?= $r['gd'] > 0 ? '+' : '' ?><?= $r['gd'] ?></td>
            <td class="num hide-sm muted"><?= $r['pct'] ?></td>
            <td class="hide-sm">
              <span class="form">
                <?php foreach ($r['form'] as $f): ?><b class="<?= $f ?>" title="<?= ['V' => 'Vitória', 'E' => 'Empate', 'D' => 'Derrota'][$f] ?>"><?= $f ?></b><?php endforeach; ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="legend">
      <span><i style="background:#2f5bff"></i> Libertadores (1º–4º)</span>
      <span><i style="background:#12a36b"></i> Sul-Americana (5º–6º)</span>
      <span><i style="background:var(--loss)"></i> Rebaixamento</span>
    </div>
  </section>

  <aside class="card">
    <div class="card-head"><h2><?= icon('target', 16) ?> Artilharia</h2></div>
    <?php foreach ($scorers as $i => $s): ?>
      <a class="scorer-row" href="<?= url('player', ['id' => $s['id']]) ?>">
        <span class="rank"><?= $i + 1 ?></span>
        <?= badge(['name' => $s['team_name']] + $s, 'sm') ?>
        <span class="grow">
          <strong class="ellipsis" style="display:block"><?= e($s['name']) ?></strong>
          <span class="small muted"><?= e($s['team_name']) ?> · <?= e(position_label($s['position'])) ?></span>
        </span>
        <span class="g num"><?= $s['goals'] ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$scorers): ?><div class="card-empty">Sem gols registrados.</div><?php endif; ?>
  </aside>
</div>
