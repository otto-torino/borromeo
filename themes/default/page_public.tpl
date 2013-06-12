<!DOCTYPE html>
<html lang="{LANGUAGE}">
<head>
<meta charset="utf-8" />
<meta name="description" content="{DESCRIPTION}" />
<meta name="keywords" content="{KEYWORDS}" />
{META}
<title>{TITLE}</title>
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
<div id="content">
  {module:url_module method:url_method}
</div>
<footer>
  {module:page method:view params:credits}
</footer>
<div>{ERRORS}</div>
</body>
</html>