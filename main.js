$(document).ready(function() {
	$('div.counter a.active').on('click', function(e) {
		e.preventDefault();
		updateCount($(this).closest('div.counter'));
	});

	$('img[data-alt-src]').each(function() { 
        new Image().src = $(this).data('alt-src'); 
    }).hover(sourceSwap, sourceSwap); 

    var evtSource = new EventSource("getCount.php");
    evtSource.onmessage = function(e) {
		var response = JSON.parse(e.data);
		$('div.counter').each(function() {
			var id = $(this).attr('id');
			updateCountText($(this), response[id], false);
		});
    };

    $('select#theme-select').on('change', function(e) {
    	window.location.search = $.query.set("theme", $(this).val());
    });
});

var sourceSwap = function () {
    var $this = $(this);
    var newSource = $this.data('alt-src');
    $this.data('alt-src', $this.attr('src'));
    $this.attr('src', newSource);
}

function playAudio($counterDiv) {
	if ($('input#enable-audio').is(':checked')) {
		$counterDiv.find('audio')[0].play();
	}
}

function updateCount($counterDiv) {
	var $link = $counterDiv.find('a.updateLink');
	if (!$link.hasClass('active')) {
		return;
	}

	playAudio($counterDiv);

	$link.removeClass('active');
	var url = $link.attr('href');

	$.ajax({
		'url': url,
		'success': function(result) {
			updateCountText($counterDiv, result, true);
		}
	});	
}

function updateCountText($counterDiv, newCount, clickEvent) {
	var $link = $counterDiv.find('a.updateLink')
	var $countSpan = $counterDiv.find('span.count');

	if ($countSpan.html() !== newCount) {

		if (!clickEvent) {
			playAudio($counterDiv);
		}

		$countSpan.fadeOut(200, function() {
			$countSpan.html(newCount);
			$countSpan.fadeIn(200, function() {
				$link.addClass('active');
			});
		});
	} else {
		$link.addClass('active'); 
	}
}