<h3><?= ucfirst(__('images')) ?></h3>
<?= $delete_images ?>
<?= $form_add_image_fieldset ?>
<script>
  function addImageFieldset() {

    var new_fieldset = this.getParent('fieldset').clone().inject(this.getParent('fieldset'), 'after');
    new_fieldset.getChildren('input[type=file]')[0].setProperty('value', '');

  }
</script>

