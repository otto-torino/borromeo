<div class="tab">
  <h3 class="tab-title"><?= ucfirst(__('realtimePad')) ?></h3>
  <?= implode('', $controllers) ?>
   <iframe src="<?= $pad_url ?>" frameborder="0" width="96%" height="400px" style="margin: 20px 0; border: 1px solid #eee;">
    Your browser doesn't support iframes
  </iframe>
</div>
