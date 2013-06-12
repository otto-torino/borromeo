<section>
  <h1><?= ucfirst(__('documents')) ?></h1>
  <? if(count($docs)): ?>
    <dl>
    <? foreach($docs as $doc): ?>
      <? if($doc->canView()): ?>
      <dt><a href="<?= $base_doc_url.$doc->id ?>"><?= htmlVar($doc->title) ?></a> (<?= $registry->dtime->view($doc->last_edit_date) ?>)</dt>
        <dd><?= htmlVar($doc->abstract) ?></dd>
      <? endif ?>
    <? endforeach ?>
    </dl>
  <? endif ?>
</section>
