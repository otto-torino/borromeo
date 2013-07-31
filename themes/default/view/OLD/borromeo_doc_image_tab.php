<div class="tab">
  <h3 class="tab-title"><?= ucfirst(__('images')) ?></h3>
  <div id="images-gallery-<?= $revision->id ?>"></div>
  <script>
    <? if(count($images)): ?>
      window.addEvent('domready', function() {
        var mg_instance = new moogallery('images-gallery-<?= $revision->id ?>', [
          <? foreach($images as $image): ?>
          {
            thumb: '<?= $image->thumb_path ?>',
            img: '<?= $image->path ?>',
            title: '<?= jsVar($image->title) ?>',
            description: '<?= jsVar($image->caption) ?>'
          },
          <? endforeach ?>
        ]);
      });
    <? endif ?>
  </script>
</div>
