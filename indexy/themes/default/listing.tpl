<header>
	<h1>{$paths.host}{if $paths.root !== '/'}{$paths.root}{/if} Files</h1>
	<nav>
		<ul id="path" class="cf">
			<li><a href="{$paths.root}" class="root">Home</a></li>
			{if $paths.request !== $paths.root}
			{loop $paths.segments}
			<li><a href="{$full}">{$part}</a></li>
			{/loop}
			{/if}
		</ul>
	</nav>
</header>

<section id="main">
	<table id="listing">
		<thead>
			<tr>
				<th scope="col">Name</th>
				<th scope="col">Size</th>
				<th scope="col">Modified</th>
			</tr>
		</thead>
		<tbody>
			{if $paths.request != $paths.root}
			<tr>
				<td class="name"><img class="icon" src="{$_.paths.theme}/img/icons/parent.png" /><a href="..">Parent directory</a></td>
				<td class="size">&nbsp;</td>
				<td class="modified">&nbsp;</td>
			</tr>
			{/if}
			{loop $objects.directories}
			<tr>
				<td class="name"><img class="icon" src="{$_.paths.theme}/img/icons/directory.png" /><a href="{$name}">{$name}</a></td>
				<td class="size">&nbsp;</td>
				<td class="modified">{date_format $mtime '%b %e, %Y %l:%M %p'}</td>
			</tr>
			{/loop}
			{loop $objects.files}
			<tr>
				<td class="name"><img class="icon" src="{$_.paths.theme}/img/icon.php?{$extension}" /><a href="{$name|escape:url}">{$name}</a></td>
				<td class="size">{size_format $size}</td>
				<td class="modified">{date_format $mtime '%b %e, %Y %l:%M %p'}</td>
			</tr>
			{/loop}
		</tbody>
	</table>
	
	<footer>
		{if $objects.count > 0}
		<p>There {if $objects.count > 1}are{else}is{/if} {$objects.count} object{if $objects.count > 1}s{/if}{if $objects.size > 0} totaling {size_format $objects.size}{/if} in this directory.</p>
		{else}
		<p>This directory is empty.</p>
		{/if}
	</footer>

</section>