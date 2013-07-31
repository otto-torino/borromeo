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
    <div id="content">
      <div class="grid grid-l left half">
        {module:borromeo method:index}
      </div>
      <div class="grid grid-r right half">
        {module:news method:last}
        {module:login method:login}
      </div>
      <div class="clear"></div>
    </div>
    <footer>
      {module:page method:view params:credits}
    </footer>
    <div>{ERRORS}</div>
  </body>
</html>
