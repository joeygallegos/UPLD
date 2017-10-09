<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title><?php echo isset($title) ? $title : "UPLD" ?></title>

		<!-- Styling -->
		<link rel="stylesheet" href="/assets/css/flexboxgrid.css">
		<link rel="stylesheet" href="/assets/css/sweetalert.css">
		<link rel="stylesheet" href="http://daneden.github.io/animate.css/animate.min.css">
		
		<?php if (isset($styles)) {
			foreach ($styles as $style) {
				echo "<link rel='stylesheet' href='/assets/css/{$style}.css'>";
			}
		} ?>
		
		<!-- Scripting -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<script src="/assets/scripts/sweetalert.min.js"></script>
		<script src="/assets/scripts/clipboard.min.js"></script>
		
		<!-- Keep it non-scaleable -->
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0">
	</head>
	<?php $class = isset($tags) ? $tags[0] : ''; ?>
	<body class="<?php echo $class; ?>">