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
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script   src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
	<style type="text/css">
		h1 {
			margin: auto;
			font-size: 50px;
		}
		h2 {
			position: relative;
			top: 200px;
			left: 100px;
			pointer-events: none;
			color: #FFFFFF;
			-webkit-text-stroke-width: 1px;
   			-webkit-text-stroke-color: black;
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
		var $links = $('a.active');

		$links.on('click', function(e) {
			e.preventDefault();
			updateCount($(this), $(this).attr('href'));
		});

		function checkForUpdates() {
			$links.each(function() {
				var url = 'getCount.php?counter=' + $(this).attr('id');
				updateCount($(this), url);
			});
		}
		// window.setInterval(function() { 
		// 	checkForUpdates(); 
		// }, 5000);

		$('img[data-alt-src]').each(function() { 
	        new Image().src = $(this).data('alt-src'); 
	    }).hover(sourceSwap, sourceSwap); 
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
		var $countSpan = $ptr.parent().find('span');
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
			<div class="span6">
				<h2>Patrick: <span id="patrick-count"><?php echo $counters[patrick]; ?></span></h2>
				<a class="active" id="patrick" href="?counter=patrick"><img src="cat-gif-1-1.gif" data-alt-src="cat-gif-1-2.gif" /></a>
			</div>
			<div class="span6">
				<h2>Yingying: <span><?php echo $counters[yingying]; ?></span></h2>
				<a class="active" id="yingying" href="?counter=yingying"><img src="cat-gif-2-1.gif" data-alt-src="cat-gif-2-2.gif" /></a>
			</div>
		</div>
	</div>
</body>

</html>
<?php
	}
?>