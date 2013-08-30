<div style="text-align: left;">
<span class="link icon icon_pad" id="icon_pad" onclick="borromeo.togglePad()" title="<?= preg_replace('#"#', "'", __('togglePad')) ?>"></span>
</div>
<div id="borromeo-doc-pad">
  <section>
    <h3 class="tab-title"><?= ucfirst(__('realtimeNotes')) ?> - <?= htmlVar($content->subchapter()->title) ?></h3>
    <iframe src="<?= $pad_url ?>" frameborder="0" width="96%" height="400px" style="margin: 20px 0; border: 1px solid #eee;">
      Your browser doesn't support iframes
    </iframe>
  </section>
</div>
