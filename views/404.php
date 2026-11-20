<?php /** @var string $message */ ?>
<div class="card empty-state">
  <div class="big">404</div>
  <h2 style="margin:8px 0 6px"><?= e($message) ?></h2>
  <p class="muted">O link pode estar quebrado ou o registro foi removido.</p>
  <a class="btn btn-primary mt" href="<?= url() ?>"><?= icon('home', 16) ?> Voltar ao início</a>
</div>
