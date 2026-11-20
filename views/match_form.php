<?php
/** @var array|null $m @var array $teams @var array $leagues */
$v = fn (string $k, $default = '') => old($k, $m[$k] ?? $default);
$date = $v('match_date', date('Y-m-d 16:00'));
$date = str_replace(' ', 'T', substr((string) $date, 0, 16));
$defaultLeague = $leagues[0]['id'] ?? '';
?>
<nav class="breadcrumb">
  <a href="<?= url('matches') ?>">Partidas</a> /
  <?php if ($m): ?><a href="<?= url('match', ['id' => $m['id']]) ?>"><?= e($m['home_name']) ?> x <?= e($m['away_name']) ?></a> /<?php endif; ?>
  <span><?= $m ? 'Editar' : 'Nova' ?></span>
</nav>

<div class="page-head">
  <div>
    <h1><?= $m ? 'Editar partida' : 'Nova partida' ?></h1>
    <p>Defina os times, data, status e — se já começou — placar e estatísticas.</p>
  </div>
</div>

<form method="post" action="<?= url('match_save') ?>" class="card form-card">
  <?= csrf_field() ?>
  <?php if ($m): ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><?php endif; ?>

  <div class="card-body form-grid">
    <div class="field">
      <label for="home_team_id">Mandante *</label>
      <select class="input" id="home_team_id" name="home_team_id" required>
        <option value="">Selecione…</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (string) $v('home_team_id') === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="away_team_id">Visitante *</label>
      <select class="input" id="away_team_id" name="away_team_id" required>
        <option value="">Selecione…</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (string) $v('away_team_id') === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="league_id">Competição</label>
      <select class="input" id="league_id" name="league_id">
        <option value="">Amistoso / sem competição</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= $l['id'] ?>" <?= (string) $v('league_id', $defaultLeague) === (string) $l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?> <?= e($l['season']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="round">Rodada</label>
      <input class="input" type="number" id="round" name="round" min="1" max="99" value="<?= e($v('round')) ?>" placeholder="Ex.: 16">
    </div>

    <div class="field">
      <label for="match_date">Data e horário *</label>
      <input class="input" type="datetime-local" id="match_date" name="match_date" required value="<?= e($date) ?>">
    </div>
    <div class="field">
      <label for="stadium">Estádio</label>
      <input class="input" id="stadium" name="stadium" value="<?= e($v('stadium')) ?>" placeholder="Em branco = estádio do mandante">
    </div>

    <div class="form-section">Situação e placar</div>

    <div class="field">
      <label for="status">Status</label>
      <select class="input" id="status" name="status" data-status-select>
        <?php foreach (STATUSES as $k => $label): ?>
          <option value="<?= $k ?>" <?= $v('status', 'scheduled') === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" data-when-live>
      <label for="minute">Minuto atual</label>
      <input class="input" type="number" id="minute" name="minute" min="1" max="130" value="<?= e($v('minute')) ?>" placeholder="Ex.: 63">
    </div>

    <div class="field full" data-when-played>
      <label>Placar (mandante × visitante)</label>
      <div class="score-inputs">
        <input class="input" type="number" name="home_score" min="0" max="99" value="<?= e($v('home_score', 0)) ?>">
        <strong class="muted">×</strong>
        <input class="input" type="number" name="away_score" min="0" max="99" value="<?= e($v('away_score', 0)) ?>">
      </div>
      <span class="hint">Dica: ao registrar gols na página da partida o placar é atualizado sozinho.</span>
    </div>

    <div class="form-section" data-when-played>Estatísticas (opcional)</div>
    <div class="field" data-when-played>
      <label for="home_possession">Posse de bola do mandante (%)</label>
      <input class="input" type="number" id="home_possession" name="home_possession" min="0" max="100" value="<?= e($v('home_possession')) ?>" placeholder="Ex.: 55">
    </div>
    <div data-when-played></div>
    <?php foreach (['shots' => 'Finalizações', 'shots_on' => 'Finalizações no gol', 'corners' => 'Escanteios', 'fouls' => 'Faltas'] as $k => $label): ?>
      <div class="field" data-when-played>
        <label><?= $label ?> — mandante</label>
        <input class="input" type="number" name="home_<?= $k ?>" min="0" max="99" value="<?= e($v("home_{$k}")) ?>">
      </div>
      <div class="field" data-when-played>
        <label><?= $label ?> — visitante</label>
        <input class="input" type="number" name="away_<?= $k ?>" min="0" max="99" value="<?= e($v("away_{$k}")) ?>">
      </div>
    <?php endforeach; ?>
  </div>

  <div class="form-actions">
    <a class="btn btn-ghost" href="<?= $m ? url('match', ['id' => $m['id']]) : url('matches') ?>">Cancelar</a>
    <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Salvar partida</button>
  </div>
</form>
