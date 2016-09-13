<html>

<?php
   $dbhost = "sql213.hostingmyself.com";
   $dbuser = "hmsfo_18881848";
   $dbpass = "freescripthostpass";
   $dbname = "hmsfo_18881848_counter";
   
   //Connect to MySQL Server
   $conn = mysql_connect($dbhost, $dbuser, $dbpass);
   
   //Select Database
   mysql_select_db($dbname) or die(mysql_error());
   
   //build query
   $query = "SELECT * FROM counters";
   
   //Execute query
   $qry_result = mysql_query($query) or die(mysql_error());

   $counters = array();

   while($row = mysql_fetch_array($qry_result)) {
     $counters[$row[counter_name]] = $row[count];
   }

   $updateCounter = $_GET['counter'];
   $isUpdateRequest = isset($updateCounter);
   if ($isUpdateRequest) {
   	 $counters[$updateCounter]++;
     $query = "UPDATE `counters` SET `count`=$counters[$updateCounter] WHERE counter_name='$updateCounter'";
     if (! mysql_query( $query, $conn )) {
     	die('Could not update data: ' . mysql_error());
     }
   }

   if (!$isUpdateRequest) {
?>

<head>
	<title>BAE Counter</title>
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

<?php
	}
?>

</html>