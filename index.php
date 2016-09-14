<?php

	include 'dbconn.php';

	$counterToUpdate = $_GET['counter'];

	if (isset($counterToUpdate)) {
		 updateCounter($counterToUpdate);
	} else {
		$counters = getCounters();
?>
<html>

<head>
	<title>Bae Counter</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script   src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
	<style type="text/css">
		h1 {
			font-size: 50px;
			padding: 20px;
		}
		.span6 {
			position: relative;
		}
		h2 {
		    position: absolute;
		    top: 80%;
		    left: 20%;
		    margin-top: -20px; //this should be a hard value that corresponds to half the height of the text itself
		    width: 100%;
		    text-align: center;
		    pointer-events: none;
		    color: #FFFFFF;
		    -webkit-text-stroke-width: 1px;
		    -webkit-text-stroke-color: black;
		}
		img {
			height: 300px;
		}
		audio {
			display: none;
		}
		@media only screen and (max-width: 768px) {
			.container {
				width: 100%;
			}
			.span12 {
				width: 100%;
			}
			.span6 {
				width: 100%;
				padding: 5%;
			}
			h1 {
				font-size: 30px;
			}
			img {
				width: 100%;
				height: auto;
			}
		}
	</style>

	<script type="text/javascript">
	var sourceSwap = function () {
	    var $this = $(this);
	    var newSource = $this.data('alt-src');
	    $this.data('alt-src', $this.attr('src'));
	    $this.attr('src', newSource);
	}

	$(document).ready(function() {
		var $counterDivs = $('div.counter');
		var $links = $('div.counter a.active');

		$links.on('click', function(e) {
			e.preventDefault();
			updateCount($(this), $(this).attr('href'));
			$(this).parents('div.counter').find('audio')[0].play();
		});

		$('img[data-alt-src]').each(function() { 
	        new Image().src = $(this).data('alt-src'); 
	    }).hover(sourceSwap, sourceSwap); 

	    var evtSource = new EventSource("getCount.php");
	    evtSource.onmessage = function(e) {
			var response = JSON.parse(e.data);
			$counterDivs.each(function() {
				var id = $(this).attr('id');
				updateCountText($(this).find('span.count'), response[id]);
			});
	    };
	});

	function updateCount($ptr, url) {
		if (!$ptr.hasClass('active')) {
			return;
		}

		$ptr.removeClass('active');
		$.ajax({
			'url': url,
			'success': function(result) {
				updateCountText($ptr, result);
			}
		});	
	}

	function updateCountText($ptr, newCount) {
		var $countSpan = $ptr.parents('div.counter').find('span.count');
		if ($countSpan.html() !== newCount) {

			$countSpan.fadeOut(200, function() {
				$countSpan.html(newCount);
				$countSpan.fadeIn(200, function() {
					$ptr.addClass('active');
				});
			});
		} else {
			$ptr.addClass('active'); 
		}
	}
	</script>
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="span12" style="margin-bottom: 20px;">
				<h1>Bae counter</h1>
			</div>
			<div class="span6 counter" id="patrick">
				<h2>Patrick: <span class="count"></span></h2>
				<audio controls preload="auto">
					<source src="audio/meow-1.mp3" type="audio/mpeg">
				</audio>
				<a class="active" href="?counter=patrick"><img src="img/cat-gif-1-1.gif" data-alt-src="img/cat-gif-1-2.gif" /></a>
			</div>
			<div class="span6 counter" id="yingying">
				<h2>Yingying: <span class="count"></span></h2>
				<audio controls preload="auto">
					<source src="audio/meow-2.mp3" type="audio/mpeg">
				</audio>
				<a class="active" href="?counter=yingying"><img src="img/cat-gif-3-1.gif" data-alt-src="img/cat-gif-3-2.gif" /></a>
			</div>
		</div>
	</div>
</body>

</html>
<?php
	}
?>