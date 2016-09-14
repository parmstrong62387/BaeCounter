<html>

	<head>
		<title>Bae Counter</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0,user-scalable=no"/>
	    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="screen.css"/>
		<link id="favicon" rel="shortcut icon" href="img/favicon-32.png" sizes="16x16 32x32 48x48" type="image/png" />
		<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script src="main.js" type="text/javascript"></script>
	</head>

	<body>
		<div class="container">
			<div class="row">
				<div class="span12" style="margin-bottom: 20px;">
					<h1>Bae counter</h1>
					<input type="checkbox" id="enable-audio" checked="checked"/>
					<label for="enable-audio">Enable Audio?</label>
				</div>
				<div class="span6 counter" id="patrick">
					<h2>Patrick: <span class="count"></span></h2>
					<audio controls preload="auto">
						<source src="audio/meow-1.mp3" type="audio/mpeg">
					</audio>
					<a class="active" href="updateCount.php?counter=patrick"><img src="img/cat-gif-1-1.gif" data-alt-src="img/cat-gif-1-2.gif" /></a>
				</div>
				<div class="span6 counter" id="yingying">
					<h2>Yingying: <span class="count"></span></h2>
					<audio controls preload="auto">
						<source src="audio/meow-2.mp3" type="audio/mpeg">
					</audio>
					<a class="active" href="updateCount.php?counter=yingying"><img src="img/cat-gif-3-1.gif" data-alt-src="img/cat-gif-3-2.gif" /></a>
				</div>
			</div>
		</div>
	</body>

</html>