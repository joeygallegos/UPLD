<?php
?>
<section id="login">
	<div class="grid">
		<div class="grid__col grid__col--3-of-5 grid__col--centered">
			<form id="login" action="/ajax/login/" method="post" class="animated fadeIn">
				<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
				<input class="full" name="username" type="text" placeholder="Username">
				<input class="full" name="password" type="password" placeholder="Password">
				<button class="button full">Go!</button>
			</form>
		</div>
	</div>
</section>

<section id="about">
	<div class="grid">
		<div class="grid__col grid__col--3-of-5 grid__col--centered">
			<h3>A private info repository system created by <a href="http://github.com/joeygallegos">Joey Gallegos</a></h3>
		</div>
	</div>
</section>

<script type="text/javascript" src="/assets/scripts/front.js"></script>