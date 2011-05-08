<header>
	<h1>{$error}</h1>
</header>

<section id="main">
	<footer>
	{if $error === 'HTTP/1.1 404 Not Found'}
		<p>This directory doesn&rsquo;t exist. <a href="{$paths.root}">Start from the beginning?</a></p>
	{elseif $error === 'HTTP/1.1 403 Forbidden'}
		<p>Sorry, you don&rsquo;t have permission to view this directory.</p>
	{else}
		<p>Something wen&rsquo;t awry. <a href="{$paths.root}">Start from the beginning?</a></p>
	{/if}
	</footer>
</section>