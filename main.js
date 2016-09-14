var sourceSwap = function () {
    var $this = $(this);
    var newSource = $this.data('alt-src');
    $this.data('alt-src', $this.attr('src'));
    $this.attr('src', newSource);
}

$(document).ready(function() {
	var $counterDivs = $('div.counter');
	var $links = $('div.counter a.active');
	var $enableAudio = $('input#enable-audio');

	$links.on('click', function(e) {
		e.preventDefault();
		
		updateCount($(this), $(this).attr('href'));
		
		if ($enableAudio.is(':checked')) {
			$(this).parents('div.counter').find('audio')[0].play();
		}
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