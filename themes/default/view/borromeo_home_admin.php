<section>
<nav>
  <h1><?= ucfirst(__('documents')) ?></h1>
  <ul>
    <? if($permission == 'manage'): ?>
    <li><?= $manage_doc_ctg_link ?></li>
    <? endif ?>
    <li><?= $manage_doc_link ?></li>
  </ul>
</nav>
</section>
