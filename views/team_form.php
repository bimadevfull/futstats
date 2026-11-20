<?php
/** @var array|null $t @var array $leagues */
$v = fn (string $k, $default = '') => old($k, $t[$k] ?? $default);
?>
<nav class="breadcrumb">
  <a href="<?= url('teams') ?>">Times</a> /
  <?php if ($t): ?><a href="<?= url('team', ['id' => $t['id']]) ?>"><?= e($t['name']) ?></a> /<?php endif; ?>
  <span><?= $t ? 'Editar' : 'Novo' ?></span>
</nav>

<div class="page-head">
  <div>
    <h1><?= $t ? 'Editar time' : 'Novo time' ?></h1>
    <p>As cores escolhidas geram o escudo e o tema da página do clube.</p>
  </div>
</div>

<form method="post" action="<?= url('team_save') ?>" class="card form-card">
  <?= csrf_field() ?>
  <?php if ($t): ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><?php endif; ?>

  <div class="card-body form-grid">
    <div class="field">
      <label for="name">Nome *</label>
      <input class="input" id="name" name="name" required maxlength="60" value="<?= e($v('name')) ?>" placeholder="Ex.: Esporte Clube Exemplo" data-preview-name>
    </div>
    <div class="field">
      <label for="short_name">Sigla * <span class="hint">(2–4 letras)</span></label>
      <input class="input" id="short_name" name="short_name" required maxlength="4" value="<?= e($v('short_name')) ?>" placeholder="EXE" style="text-transform:uppercase" data-preview-short>
    </div>

    <div class="field">
      <label for="league_id">Competição</label>
      <select class="input" id="league_id" name="league_id">
        <option value="">Nenhuma</option>
        <?php foreach ($leagues as $l): ?>
          <option value="<?= $l['id'] ?>" <?= (string) $v('league_id', $leagues[0]['id'] ?? '') === (string) $l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?> <?= e($l['season']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="founded">Fundação</label>
      <input class="input" type="number" id="founded" name="founded" min="1850" max="<?= date('Y') ?>" value="<?= e($v('founded')) ?>" placeholder="Ex.: 1912">
    </div>

    <div class="field">
      <label for="city">Cidade</label>
      <input class="input" id="city" name="city" maxlength="60" value="<?= e($v('city')) ?>">
    </div>
    <div class="field">
      <label for="stadium">Estádio</label>
      <input class="input" id="stadium" name="stadium" maxlength="80" value="<?= e($v('stadium')) ?>">
    </div>

    <div class="field full">
      <label for="coach">Técnico</label>
      <input class="input" id="coach" name="coach" maxlength="60" value="<?= e($v('coach')) ?>">
    </div>

    <div class="form-section">Identidade visual</div>
    <div class="field">
      <label for="color_primary">Cor principal</label>
      <input class="input" type="color" id="color_primary" name="color_primary" value="<?= e(safe_color($v('color_primary', '#3b5bfd'))) ?>" data-preview-c1>
    </div>
    <div class="field">
      <label for="color_secondary">Cor secundária</label>
      <input class="input" type="color" id="color_secondary" name="color_secondary" value="<?= e(safe_color($v('color_secondary', '#ffffff'), '#ffffff')) ?>" data-preview-c2>
    </div>
    <div class="full color-preview">
      <span data-preview-badge><?= badge(['name' => $v('name', 'Time'), 'short_name' => $v('short_name', 'NEW'), 'color_primary' => $v('color_primary', '#3b5bfd'), 'color_secondary' => $v('color_secondary', '#ffffff')], 'lg') ?></span>
      <div>
        <strong data-preview-label><?= e($v('name') ?: 'Prévia do escudo') ?></strong>
        <div class="small muted">É assim que o escudo aparece nas listas, placares e tabela.</div>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <a class="btn btn-ghost" href="<?= $t ? url('team', ['id' => $t['id']]) : url('teams') ?>">Cancelar</a>
    <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Salvar time</button>
  </div>
</form>
