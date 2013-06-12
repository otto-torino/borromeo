<article class="borromeo-doc-subchapter">
  <h2><?= htmlVar($subchapter->title) ?></h2>
  <div id="tab-<?= $subchapter->id ?>">
    <?= $content_tab ?>
    <?= $pad_tab ?>
    <?= $revision_tab ?>
    <?= $revision_history_tab ?>
  </div>
</article>
<script type="text/javascript">
  new mootab('tab-<?= $subchapter->id ?>', '.tab', '.tab-title');
</script>
