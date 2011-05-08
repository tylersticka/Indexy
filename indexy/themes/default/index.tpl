<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Index of {$paths.request}{if $paths.request !== '/'}/{/if}</title>
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Droid+Sans" />
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald" />
	<link rel="stylesheet" href="{$paths.theme}/css/style.css" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>

{if $error}
	{include(error.tpl)}
{else}
	{include(listing.tpl)}
{/if}

<footer id="byline">
	<p>Indexy and its default theme were created by <a href="http://tylersticka.com">Tyler Sticka</a>.</p>
	<p>File icons by <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a>, licensed under a <a href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 license</a>.</p>
</footer>

{* The following is commented out until we get table sorting or something else in here.

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="{$paths.theme}/js/libs/jquery-1.6.min.js">\x3C/script>')</script>
<script src="{$paths.theme}/js/plugins/jquery.tablesorter.min.js"></script>
<script src="{$paths.theme}/js/script.js"></script>

*}

</body>
</html>