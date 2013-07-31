<div class="tab">
  <h3 class="tab-title"><?= ucfirst(__('attachments')) ?></h3>
  <? if(count($attachments)): ?>
    <table class="generic">
      <tr>
        <th><?= __('filename') ?></th>
        <th><?= __('title') ?></th>
        <th><?= __('description') ?></th>
      </tr>
      <? foreach($attachments as $attachment): ?>
      <tr>
        <td><a href="<?= $attachment->path ?>"><?= htmlVar($attachment->filename) ?></a></td>
        <td><?= htmlVar($attachment->title) ?></td>
        <td><?= htmlVar($attachment->description) ?></td>
      </tr>
      <? endforeach ?>
    </table>
  <? endif ?>
</div>
