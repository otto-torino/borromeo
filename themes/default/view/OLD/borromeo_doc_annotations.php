<span class="link icon icon_notes" id="icon_notes" onclick="borromeo.toggleNotes()" title="<?= preg_replace('#"#', "'", __('toggleAnnotations')) ?>"></span>
<section class="borromeo-doc-annotations" id="borromeo-doc-annotations">
  <header>
    <h1 class="left"><?= ucfirst(__('docAnnotations')) ?></h1>
    <span class="right link icon icon_add" onclick="<?= layerWindowCall(ucfirst(__('addNote')), $new_note_url) ?>" title="<?= __('addNote') ?>"></span>
    <div class="clear"></div>
  </header>
  <p>
    <select onchange="ajaxRequest('post', '<?= $registry->router->linkAjax('borromeo', 'docAnnotations')?>', 'subchapter_id=' + $(this).value, 'borromeo-doc-notes-container', {load: 'borromeo-doc-annotations', load_replace: true})">
    <? foreach($select_subchapters as $sid => $stitle): ?>
      <option value="<?= $sid ?>"<? if($sid == $subchapter->id) echo " selected=\"selected\""; ?>><?= $stitle ?></option>
    <? endforeach ?>
    </select>
  </p>
  <? if(count($notes)): ?>
    <dl class='collapsable-dl'>
    <? foreach($notes as $note): ?>
      <? $nuser = new user($note->user); ?>
      <dt class="link">
        <h2>&#149; <?=htmlVar($note->title) ?><br />&#160; <time style="font-weight: normal"><?= $registry->dtime->view($note->last_edit_date, 'datetime') ?></time></h2>
        <dd>
        <div>
          <?= htmlVar($nuser->lastname.' '.$nuser->firstname) ?>
        </div>
          <?= htmlVar($note->text) ?>
          <? if(count($note->files())): ?>
            <ol>
              <? foreach($note->files() as $file): ?>
                <li><a href="<?= $file->path ?>"><?= htmlVar($file->title) ?></a></li>
              <? endforeach ?>
            </ol>
          <? endif ?>
          <div style="margin-top: 10px;">
            <span class="link icon_small icon_edit_small" onclick="<?= layerWindowCall(ucfirst(__('editNote')), $edit_url.'&note='.$note->id) ?>"></span>
            <span class="link icon_small icon_delete_small" onclick="<?= layerWindowCall(ucfirst(__('deleteNote')), $delete_url.'&note='.$note->id) ?>"></span>
          </div>
      </dd>
    <? endforeach ?>
    </table>
  <? endif ?>
    <script type="text/javascript">

      (function() {
        var notes_c = document.id('borromeo-doc-notes-container');
        var scroll_offset = notes_c.getCoordinates().top;
        var myscrollfx = new Fx.Tween(notes_c, {'duration': 'short', 'link': 'cancel'});

        window.addEvent('scroll', function() {
          var scroll = window.getScroll().y;
          if(scroll > scroll_offset) {
            myscrollfx.start('margin-top', (scroll + 20) - scroll_offset);
          }
          else {
            myscrollfx.start('margin-top', 10);
          }
        })
      })()

      $$('.collapsable-dl').each(function(dl) {
        dl.getChildren('dd').setStyle('display', 'none');
        dl.getChildren('dt').each(function(dt) {
          dt.addEvent('click', function() {
            dt.getNext('dd').toggle();
          })
        })
      })

   </script>
</section>
