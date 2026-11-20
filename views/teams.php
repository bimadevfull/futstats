<?php /** @var array $teams @var array $positions */ ?>
<div class="page-head">
  <div>
    <h1>Times</h1>
    <p><?= count($teams) ?> clubes cadastrados</p>
  </div>
  <div class="row">
    <input type="search" class="input" placeholder="Filtrar times…" data-filter=".team-card" style="width:220px">
    <a class="btn btn-primary" href="<?= url('team_form') ?>"><?= icon('plus', 16) ?> Novo time</a>
  </div>
</div>

<div class="grid-3">
  <?php foreach ($teams as $t): $row = $positions[$t['id']] ?? null; ?>
    <a class="team-card" href="<?= url('team', ['id' => $t['id']]) ?>"
       style="--c1: <?= safe_color($t['color_primary']) ?>; --c2: <?= safe_color($t['color_secondary'], '#ffffff') ?>"
       data-search="<?= e(mb_strtolower($t['name'] . ' ' . $t['short_name'] . ' ' . $t['city'])) ?>">
      <div class="top">
        <?= badge($t, 'lg') ?>
        <div class="grow">
          <h3 class="ellipsis"><?= e($t['name']) ?></h3>
          <div class="sub ellipsis"><?= e($t['city'] ?: '—') ?><?= $t['stadium'] ? ' · ' . e($t['stadium']) : '' ?></div>
        </div>
      </div>
      <div class="facts">
        <div class="fact"><b><?= $row ? $row['pos'] . 'º' : '—' ?></b><span>Posição</span></div>
        <div class="fact"><b><?= $row ? $row['pts'] : '—' ?></b><span>Pontos</span></div>
        <div class="fact"><b><?= (int) $t['players_count'] ?></b><span>Elenco</span></div>
      </div>
      <?php if ($row && $row['form']): ?>
        <div class="row-between">
          <span class="small muted">Últimos jogos</span>
          <span class="form"><?php foreach ($row['form'] as $f): ?><b class="<?= $f ?>"><?= $f ?></b><?php endforeach; ?></span>
        </div>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (!$teams): ?>
  <div class="card empty-state">
    <div class="big">0</div>
    <p class="muted">Nenhum time cadastrado ainda.</p>
    <a class="btn btn-primary" href="<?= url('team_form') ?>"><?= icon('plus', 16) ?> Cadastrar o primeiro</a>
  </div>
<?php endif; ?>
