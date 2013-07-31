<h3><?= htmlVar($subchapter->title) ?></h3>
<div id="tab-<?= $subchapter->id ?>">
  <?= $content_tab ?>
  <?= $pad_tab ?>
  <?= $revision_tab ?>
  <?= $revision_history_tab ?>
</div>
<script type="text/javascript">
  new mootab('tab-<?= $subchapter->id ?>', '.tab', '.tab-title');
</script>
