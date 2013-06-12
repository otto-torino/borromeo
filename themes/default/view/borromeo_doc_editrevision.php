<?
  $subchapter = $content->subchapter();
  $chapter = $subchapter->chapter();
  $doc = $chapter->doc();
?>
<section>
  <header>
    <h1><?= $revision_id ? ucfirst(__('revision')).'#'.$revision_id : ucfirst(__('newRevision')) ?></h1>
    <h2><?= htmlVar($doc->title) ?> &#155; <?= htmlVar($chapter->title) ?> &#155; <?= htmlVar($subchapter->title) ?></h2>
  </header>
  <? if($content->canManage()): ?>
    <p><?= $create_etherpad ?></p>
  <? endif ?>
  <?= $sform ?>
  <h3><?= ucfirst(__('content')) ?></h3>
  <?= $text_form ?>
  <?= implode('', $media_forms) ?>
  <?= $cform ?>
</section>
