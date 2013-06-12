<section class="news">
<h1><?= ucfirst(__('news')) ?></h1>
<? if(count($news)): ?>
<ul class="news">
<? foreach($news as $n): ?>
<li>
<header>
<time pubdate="pubdate"><?= $n['date'] ?></time>
<h2><?= $n['title'] ?></h2>
</header>
<div class="n_readall" data-title="<?= $n['title'] ?>">
<? if($n['thumb']): ?>
<img class="left" style="margin-right: 5px;" src="<?= $n['thumb'] ?>" />
<? endif ?>
<div><?= $n['text'] ?></div>
</div>
<div class="clear"></div>
<p class="line"></p>
</li>
<? endforeach ?>
</ul>
<p style="text-align:right"><?= $link_archive ?></p>
<? endif ?>
<script>
ajs.use(['ajs.tools.mooreadall'], function() {
	// the library is ready
	var mr = new ajs.tools.mooreadall({});
	mr.add('.n_readall', {truncate_characters: '... ', action_label: '<?= jsVar(__('readAll')) ?>', words: 10, layer_draggable: false, layer_resizable: false, layer_text_resizable: false});
})
</script>
</section>
