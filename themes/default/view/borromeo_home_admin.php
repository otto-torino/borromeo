<section>
<nav>
  <h1><?= ucfirst(__('documents')) ?></h1>
  <ul>
    <? if($can_manage): ?>
    <li><?= $manage_doc_ctg_link ?></li>
    <? endif ?>
    <? if($can_create): ?>
    <li><?= $manage_doc_link ?></li>
    <? endif ?>
    <? if($can_publish): ?>
    <li><?= $manage_notes_link ?></li>
    <? endif ?>
  </ul>
</nav>
</section>
