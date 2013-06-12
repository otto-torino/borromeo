<section class="news">
<h1><?= __('NewsArchive') ?></h1>
<? foreach($news as $n): ?>
<header>
<time pubdate="pubdate"><?= $n['date'] ?></time>
<h2><?= $n['title'] ?></h2>
</header>
<div>
<? if($n['image']): ?>
<img class="left" style="max-width: 300px; margin-right: 10px;" src="<?= $n['image'] ?>" />
<? endif ?>
<?= $n['text'] ?>
</div>
<div class="clear"></div>
<p class="line"></p>
<? endforeach ?>
<div class="left">
<?= $pnavigation ?>
</div>
<div class="right">
<?= $psummary ?>
</div>
<div class="clear"></div>
</section>
