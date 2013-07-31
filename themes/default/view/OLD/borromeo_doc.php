<?= $controllers ?>
<section id="borromeo-doc">
  <h1><?= htmlVar($doc->title) ?></h1>
  <? if($chapter): ?>
    <h2><?= htmlVar($chapter->title) ?></h2>
    <div class="borromeo-doc-subchapter">
      <?= $doc_subchapter ?>
    </div>
    <div class="clear"></div>
  <? else: ?>
    <p><?= ucfirst(__('noChapters')) ?></p>
  <? endif ?>
</section>
