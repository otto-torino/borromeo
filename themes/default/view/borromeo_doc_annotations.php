<span class="link icon icon_notes" id="icon_notes" onclick="borromeo.toggleNotes()" title="<?= preg_replace('#"#', "'", __('toggleAnnotations')) ?>"></span>
<section class="borromeo-doc-annotations" id="borromeo-doc-annotations">
  <header>
    <h1 class="left"><?= ucfirst(__('docAnnotations')) ?></h1>
    <span class="right link icon icon_add" onclick="<?= layerWindowCall(ucfirst(__('addNote')), $new_note_url, array('maxheight'=>600)) ?>" title="<?= __('addNote') ?>"></span>
    <div class="clear"></div>
  </header>
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
                <li><a target="_blank" href="<?= $file->path ?>"><?= htmlVar($file->title) ?></a></li>
              <? endforeach ?>
            </ol>
          <? endif ?>
          <div style="margin-top: 10px;">
            <span class="link icon_small icon_edit_small" onclick="<?= layerWindowCall(ucfirst(__('editNote')), $edit_url.'&note='.$note->id) ?>"></span>
            <a class="link icon_small icon_delete_small" href="<?= $delete_url.'&note='.$note->id ?>"></a>
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
            myscrollfx.start('margin-top', (scroll + 50) - scroll_offset);
          }
          else {
            myscrollfx.start('margin-top', 40);
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
