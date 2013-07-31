<section>
<hgroup>
  <h1><?= htmlVar($doc->title) ?></h1>
  <h2><?= htmlVar($chapter->title) ?></h2>
</hgroup>
<h3><?= htmlVar($subchapter->title) ?></h3>
<? if($can_edit): ?>
  <div contenteditable="true" id="editable">
<? endif ?>
<? if($revision->text == ''): ?>
  <p><?= __('clickHereToInsertText') ?></p>
<? else: ?>
  <?= htmlVar($revision->text) ?>
<? endif ?>
<? if($can_edit): ?>
  </div>
<? endif ?>
</section>
<script>

  // enable save icon only when the editable div is clicked for the first time
  var save_revision_icon = document.id('borromeo-doc-controllers').getElements('.icon_save')[0];
  save_revision_icon.store('onclick', save_revision_icon.get('onclick'));
  save_revision_icon.onclick = '';
  document.id('editable').addEvent('click', function() {
    save_revision_icon.removeClass('inactive');
    save_revision_icon.onclick = function() { eval(save_revision_icon.retrieve('onclick')); }
  })
    // Turn off automatic editor creation first.
  CKEDITOR.disableAutoInline = true;
  CKEDITOR.inline( 'editable' );
  CKEDITOR.config.allowedContent = true;
  CKEDITOR.config.entities = false;
  CKEDITOR.config.filebrowserBrowseUrl = '/lib/php/ckfinder/ckfinder.html';
  CKEDITOR.config.filebrowserImageBrowseUrl = '/lib/php/ckfinder/ckfinder.html?Type=Images';
  CKEDITOR.config.filebrowserFlashBrowseUrl = '/lib/php/ckfinder/ckfinder.html?Type=Flash';
  CKEDITOR.config.filebrowserUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
  CKEDITOR.config.filebrowserImageUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
  CKEDITOR.config.filebrowserFlashUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
</script>
