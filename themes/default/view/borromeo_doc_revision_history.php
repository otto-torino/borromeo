<section>
  <h1 class="tab-title"><?= ucfirst(__('revisionHistory')) ?></h1>
  <? if(count($revisions)): ?>
  <table class="wide generic">
    <tr>
      <th>#<?= __('revisionNumber') ?></th>
      <th><?= __('author') ?></th>
      <th><?= __('lastEditDate') ?></th>
      <th><?= __('merged') ?></th>
      <th><?= __('mergedDate') ?></th>
      <th><?= __('mergedAuthor') ?></th>
      <th><?= __('mergedComment') ?></th>
      <th><?= __('createNewRevisionFrom') ?></th>
    </tr>
    <? foreach($revisions as $revision): ?>
    <?
      $ruser = new user($revision->user);
      $muser = new user($revision->merged_user);
    ?>
    <tr>
      <td>
        <? if($current_revision->id === $revision->id): ?>
          <?= $revision->id.' ('.__('current').')' ?>
        <? else: ?>
          <span class="link" onclick="<?= layerWindowCall(ucfirst(__('revision').'#'.$revision->id), $view_url.'&revision_id='.$revision->id) ?>" title="<?= __('edit') ?>"><?= $revision->id ?></span>
        <? endif ?>
      </td>
      <td><?= htmlVar($ruser->lastname.' '.$ruser->firstname) ?></td>
      <td><?= $registry->dtime->view($revision->last_edit_date, 'datetime') ?></td>
      <td><?= $revision->merged ? __('yes') : __('no') ?></td>
      <td><?= $registry->dtime->view($revision->merged_date, 'datetime') ?></td>
      <td><?= htmlVar($muser->lastname.' '.$muser->firstname) ?></td>
      <td><?= htmlVar($revision->merged_comment) ?></td>
      <td>
        <? if($current_revision->id === $revision->id): ?>
        <? else: ?>
          <a href="<?= $create_from_url.'revision_id/'.$revision->id ?>"><?= __('create') ?></a>
        <? endif ?>
      </td>
    </tr>
    <? endforeach ?>
  </table>
  <? else: ?> 
    <p><?= ucfirst(__('noRevisions')) ?></p>
  <? endif ?>
</section>
