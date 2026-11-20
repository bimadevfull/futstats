<?php
/** @var array $m @var array $events @var array $homePlayers @var array $awayPlayers @var array $standingsPos @var array $h2h */
$home   = side($m, 'home');
$away   = side($m, 'away');
$status = $m['status'];
$hs     = (int) $m['home_score'];
$as     = (int) $m['away_score'];
$tab    = $_GET['tab'] ?? 'summary';
$c1h    = safe_color($m['home_c1']);
$c1a    = safe_color($m['away_c1']);
// se as duas cores principais forem parecidas, usa a secundária do visitante nas barras
$barAway = strtolower($c1h) === strtolower($c1a) ? safe_color($m['away_c2']) : $c1a;

// Marcadores por lado (abaixo do placar)
$scorers = ['home' => [], 'away' => []];
foreach ($events as $ev) {
    if (!in_array($ev['type'], GOAL_TYPES, true)) continue;
    $isHome = (int) $ev['team_id'] === (int) $m['home_team_id'];
    if ($ev['type'] === 'gol_contra') $isHome = !$isHome;
    $suffix = $ev['type'] === 'penalti' ? ' (pên.)' : ($ev['type'] === 'gol_contra' ? ' (contra)' : '');
    $scorers[$isHome ? 'home' : 'away'][] = ($ev['player_name'] ?? 'Gol') . " {$ev['minute']}'" . $suffix;
}

$stats = [
    ['Finalizações', $m['home_shots'], $m['away_shots']],
    ['Finalizações no gol', $m['home_shots_on'], $m['away_shots_on']],
    ['Escanteios', $m['home_corners'], $m['away_corners']],
    ['Faltas', $m['home_fouls'], $m['away_fouls']],
];
$cards = ['home' => ['amarelo' => 0, 'vermelho' => 0], 'away' => ['amarelo' => 0, 'vermelho' => 0]];
foreach ($events as $ev) {
    if (isset($cards['home'][$ev['type']])) {
        $cards[(int) $ev['team_id'] === (int) $m['home_team_id'] ? 'home' : 'away'][$ev['type']]++;
    }
}
$stats[] = ['Cartões amarelos', $cards['home']['amarelo'], $cards['away']['amarelo'], true];
$stats[] = ['Cartões vermelhos', $cards['home']['vermelho'], $cards['away']['vermelho'], true];
$hasStats = $m['home_shots'] !== null || $m['home_possession'] !== null;
?>
<nav class="breadcrumb">
  <a href="<?= url('matches') ?>">Partidas</a> /
  <a href="<?= url('matches', ['round' => $m['round']]) ?>"><?= e($m['league_name'] ?? 'Amistoso') ?><?= $m['round'] ? " · {$m['round']}ª rodada" : '' ?></a>
</nav>

<section class="hero" style="--h1: <?= $c1h ?>; --h2: <?= $c1a ?>">
  <div class="scoreboard">
    <div class="sb-team">
      <a href="<?= url('team', ['id' => $home['id']]) ?>" class="sb-team">
        <?= badge($home, 'xl') ?>
        <span class="sb-name"><?= e($home['name']) ?></span>
      </a>
      <?php if (isset($standingsPos[$home['id']])): ?><span class="sb-pos"><?= $standingsPos[$home['id']]['pos'] ?>º · <?= $standingsPos[$home['id']]['pts'] ?> pts</span><?php endif; ?>
    </div>

    <div class="sb-center">
      <?php if ($status === 'scheduled'): ?>
        <div class="sb-time"><?= e(fmt_date($m['match_date'], 'H:i')) ?></div>
        <div class="sb-status"><?= e(friendly_day($m['match_date'])) ?></div>
      <?php else: ?>
        <div class="sb-score num">
          <span class="<?= $status === 'finished' && $hs < $as ? 'lose' : '' ?>"><?= $hs ?></span>
          <span class="sep">-</span>
          <span class="<?= $status === 'finished' && $as < $hs ? 'lose' : '' ?>"><?= $as ?></span>
        </div>
        <div class="sb-status">
          <?php if ($status === 'live'): ?>
            <span class="chip-live"><span class="live-dot"></span> <?= (int) $m['minute'] ?>'</span>
          <?php else: ?>
            Encerrado
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="sb-team">
      <a href="<?= url('team', ['id' => $away['id']]) ?>" class="sb-team">
        <?= badge($away, 'xl') ?>
        <span class="sb-name"><?= e($away['name']) ?></span>
      </a>
      <?php if (isset($standingsPos[$away['id']])): ?><span class="sb-pos"><?= $standingsPos[$away['id']]['pos'] ?>º · <?= $standingsPos[$away['id']]['pts'] ?> pts</span><?php endif; ?>
    </div>
  </div>

  <?php if ($scorers['home'] || $scorers['away']): ?>
    <div class="sb-scorers">
      <div class="h"><?php foreach ($scorers['home'] as $s): ?><div><?= e($s) ?></div><?php endforeach; ?></div>
      <div class="ball"><?= icon('ball', 14) ?></div>
      <div class="a"><?php foreach ($scorers['away'] as $s): ?><div><?= e($s) ?></div><?php endforeach; ?></div>
    </div>
  <?php endif; ?>

  <div class="sb-info">
    <span><?= icon('calendar', 14) ?> <?= e(fmt_date($m['match_date'], 'd/m/Y \à\s H:i')) ?></span>
    <?php if ($m['stadium']): ?><span><?= icon('pin', 14) ?> <?= e($m['stadium']) ?></span><?php endif; ?>
    <?php if ($m['league_name']): ?><span><?= icon('trophy', 14) ?> <?= e($m['league_name']) ?></span><?php endif; ?>
  </div>
</section>

<section class="card" data-tabs>
  <div class="tabs" role="tablist">
    <button data-tab="summary" class="<?= $tab === 'summary' ? 'on' : '' ?>">Resumo</button>
    <button data-tab="stats" class="<?= $tab === 'stats' ? 'on' : '' ?>">Estatísticas</button>
    <button data-tab="squads" class="<?= $tab === 'squads' ? 'on' : '' ?>">Elencos</button>
    <button data-tab="h2h" class="<?= $tab === 'h2h' ? 'on' : '' ?>">Confronto direto</button>
    <button data-tab="manage" class="<?= $tab === 'manage' ? 'on' : '' ?>">Gerenciar</button>
  </div>

  <!-- RESUMO -->
  <div class="tab-panel <?= $tab === 'summary' ? 'on' : '' ?>" data-panel="summary">
    <?php if (!$events): ?>
      <div class="card-empty"><?= $status === 'scheduled' ? 'A partida ainda não começou.' : 'Nenhum evento registrado. Use a aba “Gerenciar” para adicionar gols e cartões.' ?></div>
    <?php else: ?>
      <div class="timeline">
        <?php if ($status === 'finished'): ?><div class="tl-divider"><span>FIM · <?= $hs ?> - <?= $as ?></span></div><?php endif; ?>
        <?php if ($status === 'live'): ?><div class="tl-divider"><span style="color:var(--live)">AO VIVO · <?= (int) $m['minute'] ?>'</span></div><?php endif; ?>
        <?php
        $shownHT = false;
        foreach (array_reverse($events) as $ev):
            $isHome = (int) $ev['team_id'] === (int) $m['home_team_id'];
            if (!$shownHT && $ev['minute'] <= 45 && ($status !== 'live' || $m['minute'] > 45)):
                $shownHT = true; ?>
          <div class="tl-divider"><span>INTERVALO</span></div>
        <?php endif; ?>
          <div class="tl-row">
            <div class="tl-ev <?= $isHome ? 'home' : 'away' ?>">
              <?php if (!$isHome) echo event_icon($ev['type']); ?>
              <div class="who">
                <strong><?= e($ev['player_name'] ?? '—') ?></strong>
                <small>
                  <?= e(EVENT_TYPES[$ev['type']]) ?>
                  <?php if ($ev['assist_name']): ?> · assist. <?= e($ev['assist_name']) ?><?php endif; ?>
                </small>
              </div>
              <?php if ($isHome) echo event_icon($ev['type']); ?>
            </div>
            <span class="tl-min" style="grid-column:2;grid-row:1"><?= (int) $ev['minute'] ?>'</span>
          </div>
        <?php endforeach; ?>
        <div class="tl-divider"><span>INÍCIO</span></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- ESTATÍSTICAS -->
  <div class="tab-panel <?= $tab === 'stats' ? 'on' : '' ?>" data-panel="stats">
    <?php if (!$hasStats && $status === 'scheduled'): ?>
      <div class="card-empty">As estatísticas aparecem quando a partida começar.</div>
    <?php else: ?>
      <div class="stat-list" style="--bar-h: <?= $c1h ?>; --bar-a: <?= $barAway ?>">
        <?php if ($m['home_possession'] !== null): $hp = (int) $m['home_possession']; ?>
          <div class="stat">
            <div class="stat-top"><span class="label" style="margin:0 auto">Posse de bola</span></div>
            <div class="possession">
              <div style="width: <?= $hp ?>%; background: <?= $c1h ?>"><?= $hp ?>%</div>
              <div class="a" style="width: <?= 100 - $hp ?>%; background: <?= $barAway ?>"><?= 100 - $hp ?>%</div>
            </div>
          </div>
        <?php endif; ?>
        <?php foreach ($stats as $s):
            [$label, $h, $a] = $s;
            $lowerIsBetter = $s[3] ?? false;
            $h = (int) $h; $a = (int) $a; $max = max($h + $a, 1);
            $hBetter = $lowerIsBetter ? $h < $a : $h > $a;
            $aBetter = $lowerIsBetter ? $a < $h : $a > $h; ?>
          <div class="stat">
            <div class="stat-top">
              <span class="num <?= $hBetter ? 'better' : ($aBetter ? 'worse' : '') ?>"><?= $h ?></span>
              <span class="label"><?= e($label) ?></span>
              <span class="num <?= $aBetter ? 'better' : ($hBetter ? 'worse' : '') ?>"><?= $a ?></span>
            </div>
            <div class="stat-bars">
              <div class="stat-bar home"><i style="width: <?= pct($h, $max) ?>%"></i></div>
              <div class="stat-bar away"><i style="width: <?= pct($a, $max) ?>%"></i></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- ELENCOS -->
  <div class="tab-panel <?= $tab === 'squads' ? 'on' : '' ?>" data-panel="squads">
    <div class="grid-2" style="gap:0">
      <?php foreach ([[$home, $homePlayers], [$away, $awayPlayers]] as $i => [$team, $list]): ?>
        <div style="<?= $i === 0 ? 'border-right:1px solid var(--border)' : '' ?>">
          <div class="squad-title row" style="padding-bottom:10px"><?= badge($team, 'sm') ?> <?= e($team['name']) ?></div>
          <?php foreach ($list as $p): ?>
            <a class="player-row" href="<?= url('player', ['id' => $p['id']]) ?>">
              <span class="num-shirt"><?= e($p['number']) ?></span>
              <span class="info"><strong><?= e($p['name']) ?></strong></span>
              <span class="pos-tag pos-<?= e($p['position']) ?>"><?= e($p['position']) ?></span>
              <span class="rating <?= rating_class($p['rating'] !== null ? (float) $p['rating'] : null) ?>"><?= $p['rating'] !== null ? number_format((float) $p['rating'], 1) : '–' ?></span>
            </a>
          <?php endforeach; ?>
          <?php if (!$list): ?><div class="card-empty">Sem jogadores cadastrados.</div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CONFRONTO DIRETO -->
  <div class="tab-panel <?= $tab === 'h2h' ? 'on' : '' ?>" data-panel="h2h">
    <?php if ($h2h): ?>
      <div class="match-list"><?php foreach ($h2h as $x) echo partial('match_row', ['m' => $x]); ?></div>
    <?php else: ?>
      <div class="card-empty">Nenhum confronto anterior registrado entre os dois times.</div>
    <?php endif; ?>
  </div>

  <!-- GERENCIAR -->
  <div class="tab-panel <?= $tab === 'manage' ? 'on' : '' ?>" data-panel="manage">
    <div class="card-body">
      <div class="row-between" style="margin-bottom:18px">
        <h3 style="font-size:15px">Adicionar evento</h3>
        <div class="row">
          <a class="btn btn-sm" href="<?= url('match_form', ['id' => $m['id']]) ?>"><?= icon('edit', 14) ?> Editar partida</a>
          <?= delete_button('match_delete', (int) $m['id'], 'Excluir esta partida e todos os seus eventos?', 'Excluir partida') ?>
        </div>
      </div>

      <form method="post" action="<?= url('event_save') ?>" class="form-grid" data-event-form>
        <?= csrf_field() ?>
        <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
        <div class="field">
          <label>Tipo</label>
          <select name="type" class="input" required data-event-type>
            <?php foreach (EVENT_TYPES as $k => $label): ?><option value="<?= $k ?>"><?= e($label) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Minuto</label>
          <input type="number" name="minute" class="input" min="1" max="130" required value="<?= $status === 'live' ? (int) $m['minute'] : '' ?>" placeholder="Ex.: 37">
        </div>
        <div class="field">
          <label>Time do jogador</label>
          <select name="team_id" class="input" required data-team-select>
            <option value="<?= $home['id'] ?>"><?= e($home['name']) ?> (mandante)</option>
            <option value="<?= $away['id'] ?>"><?= e($away['name']) ?> (visitante)</option>
          </select>
        </div>
        <div class="field">
          <label>Jogador</label>
          <select name="player_id" class="input" data-player-select>
            <option value="">— Não informado —</option>
            <?php foreach ([[$home, $homePlayers], [$away, $awayPlayers]] as [$team, $list]): ?>
              <optgroup label="<?= e($team['name']) ?>" data-team="<?= $team['id'] ?>">
                <?php foreach ($list as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['number']) ?> · <?= e($p['name']) ?></option><?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field" data-assist-field>
          <label>Assistência <span class="hint">(opcional)</span></label>
          <select name="assist_player_id" class="input" data-player-select>
            <option value="">— Sem assistência —</option>
            <?php foreach ([[$home, $homePlayers], [$away, $awayPlayers]] as [$team, $list]): ?>
              <optgroup label="<?= e($team['name']) ?>" data-team="<?= $team['id'] ?>">
                <?php foreach ($list as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['number']) ?> · <?= e($p['name']) ?></option><?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field" style="justify-content:flex-end">
          <label class="check"><input type="checkbox" name="update_score" value="1" checked> Atualizar o placar automaticamente em gols</label>
        </div>
        <div class="full" style="display:flex;justify-content:flex-end">
          <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Registrar evento</button>
        </div>
      </form>
    </div>

    <?php if ($events): ?>
      <div class="table-wrap" style="border-top:1px solid var(--border)">
        <table class="table">
          <thead><tr><th>Min</th><th class="left">Evento</th><th class="left">Jogador</th><th class="left">Time</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($events as $ev): $isHome = (int) $ev['team_id'] === (int) $m['home_team_id']; ?>
            <tr>
              <td class="num"><strong><?= (int) $ev['minute'] ?>'</strong></td>
              <td class="left"><span class="row"><?= event_icon($ev['type']) ?> <?= e(EVENT_TYPES[$ev['type']]) ?></span></td>
              <td class="left"><?= e($ev['player_name'] ?? '—') ?><?= $ev['assist_name'] ? ' <span class="muted small">(assist. ' . e($ev['assist_name']) . ')</span>' : '' ?></td>
              <td class="left"><span class="row"><?= badge($isHome ? $home : $away, 'xs') ?> <?= e($isHome ? $home['name'] : $away['name']) ?></span></td>
              <td><?= delete_button('event_delete', (int) $ev['id'], 'Remover este evento? Se for gol, o placar será ajustado.', '', '<input type="hidden" name="update_score" value="1">') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
