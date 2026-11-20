<?php /** @var array $players @var array $teams @var string $q @var ?int $teamId @var string $position */ ?>
<div class="page-head">
  <div>
    <h1>Jogadores</h1>
    <p><?= count($players) ?> jogador<?= count($players) === 1 ? '' : 'es' ?> encontrados · clique nos cabeçalhos para ordenar</p>
  </div>
  <a class="btn btn-primary" href="<?= url('player_form') ?>"><?= icon('plus', 16) ?> Novo jogador</a>
</div>

<section class="card">
  <form class="filters" method="get" action="index.php">
    <input type="hidden" name="p" value="players">
    <input class="input input-sm" type="search" name="q" value="<?= e($q) ?>" placeholder="Nome ou nacionalidade…" style="max-width:260px">
    <select class="input input-sm" name="team" data-autosubmit style="width:auto">
      <option value="">Todos os times</option>
      <?php foreach ($teams as $t): ?>
        <option value="<?= $t['id'] ?>" <?= (int) $t['id'] === $teamId ? 'selected' : '' ?>><?= e($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="seg">
      <?php foreach (['' => 'Todos'] + POSITIONS as $k => $label): ?>
        <a class="<?= $position === $k ? 'on' : '' ?>" href="<?= e(url('players', array_filter(['q' => $q, 'team' => $teamId, 'position' => $k]))) ?>"><?= $k ?: $label ?></a>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-sm" type="submit"><?= icon('search', 14) ?> Buscar</button>
  </form>

  <div class="table-wrap">
    <table class="table" data-sortable>
      <thead>
        <tr>
          <th class="left" data-sort="text">Jogador</th>
          <th class="left" data-sort="text">Time</th>
          <th data-sort="text">Pos.</th>
          <th data-sort="num" class="hide-sm">Idade</th>
          <th class="left hide-sm" data-sort="text">Nac.</th>
          <th data-sort="num">Gols</th>
          <th data-sort="num" class="hide-sm">Assist.</th>
          <th data-sort="num">Nota</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($players as $p): $a = age($p['birth_date']); ?>
        <tr>
          <td class="left" data-value="<?= e($p['name']) ?>">
            <a class="team-cell hover-accent" href="<?= url('player', ['id' => $p['id']]) ?>">
              <span class="avatar"><?= e(initials($p['name'])) ?></span>
              <span><?= e($p['name']) ?> <span class="muted small">#<?= e($p['number']) ?></span></span>
            </a>
          </td>
          <td class="left" data-value="<?= e($p['team_name']) ?>">
            <a class="row hover-accent" href="<?= url('team', ['id' => $p['team_id']]) ?>"><?= badge(['name' => $p['team_name']] + $p, 'sm') ?> <?= e($p['team_name']) ?></a>
          </td>
          <td data-value="<?= ['GOL' => 1, 'DEF' => 2, 'MEI' => 3, 'ATA' => 4][$p['position']] ?>"><span class="pos-tag pos-<?= e($p['position']) ?>"><?= e($p['position']) ?></span></td>
          <td class="num hide-sm" data-value="<?= $a ?? 0 ?>"><?= $a ?? '—' ?></td>
          <td class="left hide-sm muted"><?= e($p['nationality'] ?: '—') ?></td>
          <td class="num"><strong><?= (int) $p['goals'] ?></strong></td>
          <td class="num hide-sm"><?= (int) $p['assists'] ?></td>
          <td data-value="<?= $p['rating'] ?? 0 ?>"><span class="rating <?= rating_class($p['rating'] !== null ? (float) $p['rating'] : null) ?>"><?= $p['rating'] !== null ? number_format((float) $p['rating'], 1) : '–' ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (!$players): ?><div class="card-empty">Nenhum jogador encontrado.</div><?php endif; ?>
  </div>
</section>
