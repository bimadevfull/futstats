<?php
/** @var array $m */
$home   = side($m, 'home');
$away   = side($m, 'away');
$status = $m['status'];
$hs     = (int) $m['home_score'];
$as     = (int) $m['away_score'];
$homeCls = $awayCls = '';
if ($status === 'finished') {
    $homeCls = $hs > $as ? 'win' : ($hs < $as ? 'lose' : '');
    $awayCls = $as > $hs ? 'win' : ($as < $hs ? 'lose' : '');
}
$showDate = $showDate ?? true;
?>
<a class="match-row <?= $status === 'live' ? 'is-live' : '' ?>" href="<?= e(url('match', ['id' => $m['id']])) ?>">
  <div class="mr-time">
    <?php if ($status === 'live'): ?>
      <span class="live-min"><?= (int) $m['minute'] ?>'</span>
      <div class="small">Ao vivo</div>
    <?php elseif ($status === 'finished'): ?>
      <strong><?= $showDate ? e(fmt_date($m['match_date'], 'd/m')) : 'FIM' ?></strong>
      <div class="small"><?= $showDate ? 'Encerrado' : e(fmt_date($m['match_date'], 'H:i')) ?></div>
    <?php else: ?>
      <strong><?= e(fmt_date($m['match_date'], 'H:i')) ?></strong>
      <div class="small"><?= $showDate ? e(fmt_date($m['match_date'], 'd/m')) : '—' ?></div>
    <?php endif; ?>
  </div>

  <div class="mr-teams">
    <div class="mr-team <?= $homeCls ?>"><?= badge($home, 'sm') ?><span class="name"><?= e($home['name']) ?></span></div>
    <div class="mr-team <?= $awayCls ?>"><?= badge($away, 'sm') ?><span class="name"><?= e($away['name']) ?></span></div>
  </div>

  <div class="mr-scores">
    <?php if ($status === 'scheduled'): ?>
      <span class="muted">–</span><span class="muted">–</span>
    <?php else: ?>
      <span class="<?= $homeCls === 'lose' ? 'lose' : '' ?>"><?= $hs ?></span>
      <span class="<?= $awayCls === 'lose' ? 'lose' : '' ?>"><?= $as ?></span>
    <?php endif; ?>
  </div>
</a>
