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

	<script type="text/javascript">
	$(document).ready(function() {
		var $links = $('h2 a.active');

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
		window.setInterval(function() { 
			checkForUpdates(); 
		}, 5000);
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
				<h2><a class="active" id="patrick" href="?counter=patrick">Patrick: </a><span><?php echo $counters[patrick]; ?></span></h2>
			</div>
			<div class="span6">
				<h2><a class="active" id="yingying" href="?counter=yingying">Yingying: </a><span><?php echo $counters[yingying]; ?></span></h2>
			</div>
		</div>
	</div>
</body>

</html>
<?php
	}
?>