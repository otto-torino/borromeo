<span class="link icon icon_index" id="icon_index" onclick="borromeo.toggleIndex()" title="<?= preg_replace('#"#', "'", __('toggleIndex')) ?>"></span>
<section class="borromeo-doc-index" id="borromeo-doc-index">
  <header>
    <h1 class="left"><?= ucfirst(__('docIndex')) ?></h1>
    <? if($doc->canManage()): ?>
      <span class="right link icon icon_chapter" onclick="<?= layerWindowCall(ucfirst(__('newChapter')), $form_chapter_url) ?>" title="<?= __('newChapter') ?>"></span>
    <? endif ?>
    <div class="clear"></div>
  </header>
  <? if(count($doc->chapters())): ?>
    <ul class="sortable_1">
    <? foreach($doc->chapters() as $chapter_id): ?>
      <? $chapter = new chapter($chapter_id); ?>
      <li data-id="<?= $chapter->id ?>">
        <?= htmlVar($chapter->title) ?>
        <? if($doc->canManage()): ?>
          <div class="right">
            <span class="move icon_small icon_sort_small" title="<?= __('sort') ?>"></span>
            <span class="link icon_small icon_delete_small" onclick="if(confirm('<?= jsVar(__('ConfirmDeleteChapter'))?>')) location.href='<?= $delete_chapter_url.'&chapter_id='.$chapter->id ?>';" title="<?= __('delete') ?>"></span>
            <span class="link icon_small icon_edit_small" onclick="<?= layerWindowCall(ucfirst(__('editChapter')), $form_chapter_url.'&id='.$chapter->id) ?>" title="<?= __('edit') ?>"></span>
            <span class="link icon_small icon_subchapter" onclick="<?= layerWindowCall(ucfirst(__('newSubchapter')), $form_subchapter_url.'&chapter_id='.$chapter->id) ?>" title="<?= __('newSubchapter') ?>"></span>
          </div>
          <div class="clear"></div>
        <? endif ?>
        <ul class="sortable_2" data-id="<?= $chapter->id ?>">
        <? if(count($chapter->subchapters())): ?>
            <? foreach($chapter->subchapters() as $subchapter_id): ?>
              <? $subchapter = new subchapter($subchapter_id); ?>
              <li data-id="<?= $subchapter->id ?>">
                <a href="<?= $view_subchapter_url.'chapter/'.$chapter->id.'/subchapter/'.$subchapter->id ?>"><?= htmlVar($subchapter->title) ?></a>
                <? if($doc->canManage()): ?>
                <div class="right">
                  <span class="move icon_small icon_sort_small" title="<?= __('sort') ?>"></span>
                  <span class="link icon_small icon_delete_small" onclick="if(confirm('<?= jsVar(__('ConfirmDeleteSubchapter'))?>')) location.href='<?= $delete_subchapter_url.'&subchapter_id='.$subchapter->id ?>';" title="<?= __('delete') ?>"></span>
                  <span class="link icon_small icon_edit_small" onclick="<?= layerWindowCall(ucfirst(__('editSubchapter')), $form_subchapter_url.'&chapter_id='.$chapter->id.'&id='.$subchapter->id) ?>" title="<?= __('edit') ?>"></span>
                </div>
                <div class="clear"></div>
                <? endif ?>
              </li>
            <? endforeach ?>
        <? endif ?>
          </ul>
      </li>
    <? endforeach ?>
    </ul>
    <div id="result"></div>
    <script type="text/javascript">

      (function() {
        var index_c = document.id('borromeo-doc-index-container');
        var scroll_offset = index_c.getCoordinates().top;
        var myscrollfx = new Fx.Tween(index_c, {'duration': 'short', 'link': 'cancel'});

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

      var applyOrderResult = function() {
        alert('<?= jsVar(__('orderUpdated'))?>');
        window.location.reload();
      }

      var applyOrder = function(element) {

        var slists = [];
        $$('.sortable_2').each(function(list) {
          slists.push(list.get('data-id'));
        })

        ajaxRequest('post', '<?= $apply_order_url ?>', 'chapters=' + JSON.encode(mysortables1.serialize(false, function(element, index) {
            return element.get('data-id');
          })) + '&subchapters=' + JSON.encode(mysortables2.serialize(false, function(element, index) {
            return element.get('data-id');
          })) + '&slists=' + JSON.encode(slists) , null, {callback: applyOrderResult});

      }

      <? if($doc->canManage()): ?>
      var mysortables1 = new Sortables(document.getElements('.sortable_1'), {
          handle: '.move',
          clone: false,
          revert: true,
          onComplete: applyOrder
      });
      var mysortables2 = new Sortables(document.getElements('.sortable_2'), {
          handle: '.move',
          clone: false,
          revert: true,
          onComplete: applyOrder
      });
      <? endif ?>
    </script>
  <? endif ?>
</section>
