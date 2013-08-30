<h3><?= htmlVar($subchapter->title) ?></h3>
<?= htmlVar($subchapter->content()->revision()->text) ?>
<p class="clear">
<? if($prev = $subchapter->getPrevious()): ?>
  <a class="navigation navigation-prev" href="<?= $prev->getUrl() ?>"><?= htmlVar($prev->title )?></a>
<? endif ?>
<? if($next = $subchapter->getNext()): ?>
  <a class="navigation navigation-next" href="<?= $next->getUrl() ?>"><?= htmlVar($next->title )?></a>
<? endif ?>
</p>
