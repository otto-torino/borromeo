<section>
<hgroup>
  <h1><?= htmlVar($doc->title) ?></h1>
  <? if ($chapter->id): ?>
  <h2><?= htmlVar($chapter->title) ?></h2>
  <? endif ?>
</hgroup>
<?= $content ?>
</section>
