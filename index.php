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
		img {
			height: 300px;
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
		var $countSpan = $ptr.parent().find('span.count');
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
				<a class="active" href="?counter=patrick"><img src="cat-gif-1-1.gif" data-alt-src="cat-gif-1-2.gif" /></a>
			</div>
			<div class="span6 counter" id="yingying">
				<h2>Yingying: <span class="count"></span></h2>
				<a class="active" href="?counter=yingying"><img src="cat-gif-3-1.gif" data-alt-src="cat-gif-3-2.gif" /></a>
			</div>
		</div>
	</div>
</body>

</html>
<?php
	}
?>