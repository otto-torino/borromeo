<!DOCTYPE html>
<html lang="{LANGUAGE}">
  <head>
    <meta charset="utf-8" />
    <meta name="description" content="{DESCRIPTION}" />
    <meta name="keywords" content="{KEYWORDS}" />
    {META}
    <title>{TITLE}</title>
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,900italic' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Amaranth:400,700italic' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="{FAVICON}" />
    {HEAD_LINKS}
    {CSS}
    {JAVASCRIPT}
  </head>
  <body>
    <header>
      <div id="home_logo"></div>
    </header>
    <div id="content">
      {module:login method:login}
    </div>
    <footer>
      {module:page method:view params:credits}
    </footer>
    <div>{ERRORS}</div>
  </body>
</html>
