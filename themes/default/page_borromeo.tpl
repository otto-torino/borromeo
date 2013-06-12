<!DOCTYPE html>
<html lang="{LANGUAGE}">
<head>
<meta charset="utf-8" />
<meta name="description" content="{DESCRIPTION}" />
<meta name="keywords" content="{KEYWORDS}" />
{META}
<title>{TITLE}</title>
    <link href='http://fonts.googleapis.com/css?family=Amaranth:400,700italic' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="{FAVICON}" />
{HEAD_LINKS}
{CSS}
{JAVASCRIPT}
</head>
<body>
<header>
  {module:page method:view params:header}
</header>
<nav class="main_menu">
  {module:menu method:mainMenu}
</nav>
<div class="side-panel" id="borromeo-doc-index-container">
  {module:borromeo method:docIndex}
</div>
<div class="side-panel" id="borromeo-doc-notes-container">
  {module:borromeo method:docAnnotations}
</div>
<div id="content">
  {module:url_module method:url_method}
</div>
<footer>
  {module:page method:view params:credits}
</footer>
<div>{ERRORS}</div>
</body>
</html>
