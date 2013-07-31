<section>
<h1><?= $title ?></h1>
<p><?= ucfirst(__('author')).': <b>'.$author.'</b>' ?></p>
<?= htmlVar($revision->text) ?>
