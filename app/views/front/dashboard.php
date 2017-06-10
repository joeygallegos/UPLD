<?php 

$id = $user->id;
$outEcho = "<script type='text/javascript'>const userId = {$id};</script>";
?>

<!-- Chevron Icon to pull down header goes here -->
<!-- https://gitter.im/Dogfalo/materialize -->
<nav id="menu">
	<div class="panel">
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quisquam dolorum vel nostrum tempora facere voluptatum odio deserunt ex, illo dignissimos veniam iusto qui itaque, hic, earum cumque. Ipsa maiores, veritatis?</p>
	</div>

	<form id="uploadForm" action="/ajax/upload/" method="post" enctype="multipart/form-data">
		<input id="file" type="file" name="upload" class="btn-primary full"/>
		<input id="button" type="submit" value="Upload" class="btn-primary full">
	</form>
	
	<!--<div id="comments" style="text-align: center;">
		<div style="display: none;" id="generated-comment"></div>
		<a href="javascript: void(0);" class="btn-primary full gen" data-clipboard-action="copy" data-clipboard-target="#generated-comment">GENERATE NEW COMMENT</a>
	</div>-->

	<div id="timeline"></div>
</nav>

<section id="above">
	<a href="javascript: void(0);" class="btn-primary" data-action="update-view">
		UPDATE DASHBOARD
	</a>
	<a href="javascript: void(0);" class="btn-primary" data-action="load-people">
		PEOPLE INDEX
	</a>
	<a href="javascript: void(0);" class="btn-primary">
		<span class="icon"></span>CREATE POST
	</a>
</section>

<section id="dashboard">
	<div class="column">
		<div class="third">
			<div class="big" data-load="weather" id="weather"></div>
		</div>
		<div class="third">
			<div class="big" data-load="savings">
				124.94
			</div>
		</div>
		<div class="third">
			<div class="big">
				89.00
			</div>
		</div>
	</div>
</section>

<section id="posts">
	
</section>
<?php echo $outEcho; ?>
<?php 

	$scripts = array(
		'timeago',
		'autosave',
		'masonry',
		'form',
		'countup',
		'dashboard'
	);

	foreach ($scripts as $script) {
		echo "<script type=\"text/javascript\" src=\"/assets/scripts/{$script}.js\"></script>";
	}
?>