<h3><?= ucfirst(__('attachments')) ?></h3>
<?= $delete_attachments ?>
<?= $form_add_attachment_fieldset ?>
<script>
  function addAttachmentFieldset() {

    var new_fieldset = this.getParent('fieldset').clone().inject(this.getParent('fieldset'), 'after');
    new_fieldset.getChildren('input[type=file]')[0].setProperty('value', '');

  }
</script>
