<?php
/** @var array|null $p @var array $teams @var ?int $teamId */
$v = fn (string $k, $default = '') => old($k, $p[$k] ?? $default);
?>
<nav class="breadcrumb">
  <a href="<?= url('players') ?>">Jogadores</a> /
  <?php if ($p): ?><a href="<?= url('player', ['id' => $p['id']]) ?>"><?= e($p['name']) ?></a> /<?php endif; ?>
  <span><?= $p ? 'Editar' : 'Novo' ?></span>
</nav>

<div class="page-head">
  <div>
    <h1><?= $p ? 'Editar jogador' : 'Novo jogador' ?></h1>
    <p>Gols, assistências e cartões são contabilizados a partir dos eventos das partidas.</p>
  </div>
</div>

<form method="post" action="<?= url('player_save') ?>" class="card form-card">
  <?= csrf_field() ?>
  <?php if ($p): ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><?php endif; ?>

  <div class="card-body form-grid">
    <div class="field full">
      <label for="name">Nome *</label>
      <input class="input" id="name" name="name" required maxlength="80" value="<?= e($v('name')) ?>">
    </div>

    <div class="field">
      <label for="team_id">Time *</label>
      <select class="input" id="team_id" name="team_id" required>
        <option value="">Selecione…</option>
        <?php foreach ($teams as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (string) $v('team_id', $teamId) === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="position">Posição *</label>
      <select class="input" id="position" name="position" required>
        <option value="">Selecione…</option>
        <?php foreach (POSITIONS as $k => $label): ?>
          <option value="<?= $k ?>" <?= $v('position') === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="number">Número da camisa</label>
      <input class="input" type="number" id="number" name="number" min="1" max="99" value="<?= e($v('number')) ?>">
    </div>
    <div class="field">
      <label for="rating">Nota média <span class="hint">(0 a 10)</span></label>
      <input class="input" type="number" id="rating" name="rating" min="0" max="10" step="0.1" value="<?= e($v('rating')) ?>" placeholder="Ex.: 7.2">
    </div>

    <div class="field">
      <label for="nationality">Nacionalidade</label>
      <input class="input" id="nationality" name="nationality" maxlength="40" value="<?= e($v('nationality', 'Brasil')) ?>">
    </div>
    <div class="field">
      <label for="birth_date">Data de nascimento</label>
      <input class="input" type="date" id="birth_date" name="birth_date" value="<?= e($v('birth_date')) ?>">
    </div>

    <div class="field">
      <label for="height">Altura (cm)</label>
      <input class="input" type="number" id="height" name="height" min="140" max="220" value="<?= e($v('height')) ?>" placeholder="Ex.: 181">
    </div>
    <div class="field">
      <label for="foot">Pé preferido</label>
      <select class="input" id="foot" name="foot">
        <option value="">—</option>
        <?php foreach (['Direito', 'Esquerdo', 'Ambidestro'] as $f): ?>
          <option <?= $v('foot') === $f ? 'selected' : '' ?>><?= $f ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-actions">
    <a class="btn btn-ghost" href="<?= $p ? url('player', ['id' => $p['id']]) : url('players') ?>">Cancelar</a>
    <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Salvar jogador</button>
  </div>
</form>
