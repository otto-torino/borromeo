<div id="header_logo"></div>
<hgroup style="display: none;">
  <h1>BORROMEO</h1>
  <h2>COLLABORATIVE LEARNING</h2>
</hgroup>
<p>
<? if($registry->user->id): ?>
<span><?= htmlVar($registry->user) ?></span><span class="button"><a href="?logout">logout</a></span>
<? endif ?>
<a style="float: right;margin-left: 20px;" id="powered_otto" href="http://www.otto.to.it">Otto srl</a> 
</p>
