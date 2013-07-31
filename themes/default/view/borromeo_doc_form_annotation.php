<section>
  <h1><?= htmlVar($content->subchapter()->chapter()->title.' - '.$content->subchapter()->title) ?></h1>
  <h2><?= ucfirst(__('annotation')) ?></h2>
  <? if(isset($information)): ?>
    <?= $information ?>
  <? endif ?>
  <?= $form ?>
  <script>
    window.addFileFieldset = function addFileFieldset() {

      var new_fieldset = this.getParent('fieldset').clone().inject(this.getParent('fieldset'), 'after');
      new_fieldset.getChildren('input, textarea').setProperty('value', '');

    }
  </script>
</section>
