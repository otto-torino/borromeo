<div class="tab">
  <h3 class="tab-title"><?= $title ?></h3>
  <?= implode('', $controllers) ?>
  <? if($author): ?>
    <span class="left"><?= __('author').': '.$author ?></span>
  <? endif ?>
  <div class="clear"></div>

  <? if($revision->id): ?>
    <div class="borromeo-doc-content">

      <?= htmlVar($revision->text) ?>

      <div id="media-tab-r<?= $revision->id ?>">
        <?= implode($media_tabs) ?>
      </div>

    </div>

    <script type="text/javascript">
      new mootab('media-tab-r<?= $revision->id ?>', '.tab', '.tab-title');
    </script>
  <? endif ?>
</div>
