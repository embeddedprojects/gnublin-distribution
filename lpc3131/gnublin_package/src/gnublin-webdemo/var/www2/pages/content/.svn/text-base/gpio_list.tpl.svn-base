<link rel="stylesheet" type="text/css" href="modules/gpio/style.css">

<script type="text/javascript">
$(function() {
	$('#tabs').tabs();

	function SetStatusLight(el, status) {
		el.removeClass('off');
		el.removeClass('on');

		if(status==1)
			el.addClass('on');
		else
			el.addClass('off');
	}

	function SetButton(el, status) {
		if(status==1) {
			// Relais is active
			el.val('Off');
		}else{
			el.val('On');
		}
	}

	function SetStatus(relais, status) {
		var statusLight = $('.status[relais="'+relais+'"]');
		var button = $('.btn[relais="'+relais+'"]');
		SetStatusLight(statusLight, status);
		SetButton(button, status);
	}

	function init() {
		// Get status on PageLoad
		$('.btn').each(function() {
			var relais = $(this).attr('relais');

			$.get('index.php?module=gpio&action=status&gpio='+relais, function(status) {
				SetStatus(relais, status);			
			});
		});

		// Add ActionHandler
		$('.btn').click(function() {
			var relais = $(this).attr('relais');

			$.get('index.php?module=gpio&action=set&gpio='+relais, function(status) {
				SetStatus(relais, status);
			});
		});

	}

	init();
});
</script>

<div id="tabs">
	<ul><li><a href="#tabs-1">GPIO Demo</a></li></ul>
	<div id="tabs-1">
	<table id="gpio">
		<tr><td>Relais 1</td><td><div class="status off" relais="98"></div></td><td><input type="button" class="btn" relais="98" value="Pending.."></td></tr>
		<tr><td>Relais 2</td><td><div class="status off" relais="99"></div></td><td><input type="button" class="btn" relais="99" value="Pending.."></td></tr>
		<tr><td>Relais 3</td><td><div class="status off" relais="100"></div></td><td><input type="button" class="btn" relais="100" value="Pending.."></td></tr>
		<tr><td>Relais 4</td><td><div class="status off" relais="101"></div></td><td><input type="button" class="btn" relais="101" value="Pending.."></td></tr>
		<tr><td>Relais 5</td><td><div class="status off" relais="102"></div></td><td><input type="button" class="btn" relais="102" value="Pending.."></td></tr>
		<tr><td>Relais 6</td><td><div class="status off" relais="103"></div></td><td><input type="button" class="btn" relais="103" value="Pending.."></td></tr>
		<tr><td>Relais 7</td><td><div class="status off" relais="104"></div></td><td><input type="button" class="btn" relais="104" value="Pending.."></td></tr>
		<tr><td>Relais 8</td><td><div class="status off" relais="105"></div></td><td><input type="button" class="btn" relais="105" value="Pending.."></td></tr>
	</table>

	</div>
</div>



