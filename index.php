<?php

	include 'dbconn.php';

	$counterToUpdate = $_GET['counter'];
	$counters = getCounters();

	if (isset($counterToUpdate)) {
		 $counters[$counterToUpdate]++;
		 updateCounter($counterToUpdate, $counters[$counterToUpdate]);
	} else {
?>
<html>

<head>
	<title>Bae Counter</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script   src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>

	<script type="text/javascript">
	$(document).ready(function() {
		$('h2 a.active').on('click', function(e) {
			e.preventDefault();
			var $ptr = $(this);
			if (!$ptr.hasClass('active')) {
				return;
			}

			$ptr.removeClass('active');
			var url = $ptr.attr('href');

			$.ajax({
				'url': url,
				'success': function(result) {
					updateCount($ptr, true);
				},
				'failure': function(result) {
					updateCount($ptr, false);
				}
			});
		});
	});

	function updateCount($ptr, success) {
		var $countSpan = $ptr.parent().find('span');
		$countSpan.fadeOut(200, function() {
			var newCount = Number($countSpan.html());

			if (success) {
				newCount++;
			}

			$countSpan.html(newCount);
			$countSpan.fadeIn(200, function() {
				$ptr.addClass('active');
			});
		});
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