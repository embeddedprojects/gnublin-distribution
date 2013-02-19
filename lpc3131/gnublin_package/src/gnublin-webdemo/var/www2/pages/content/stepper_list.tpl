<script type="text/javascript" src="modules/stepper/jquery.knob.js"></script>


<script type="text/javascript">
$(function() {
	$('#tabs').tabs();

	// Reset to start-position
	$.get('index.php?module=stepper&action=set&value=0');

	$('.switch').knob({
		'release': function(val) { 
			$.get('index.php?module=stepper&action=set&value='+val);
		}
	});

});
</script>

<div id="tabs">
	<ul><li><a href="#tabs-1">Schrittmotor</a></li></ul>
	<div id="tabs-1">
	<div style="display:inline-block;width:400px;color:#519DD6;font:bold 55px Arial;margin-right:50px;position:relative;top:-60px;">
	Steuern Sie den Schrittmotor &uuml;ber die rechte Schaltfläche

	</div>
	<div style="display:inline-block;margin: 30px">
		<center><input type="text" class="switch" data-width="400" data-height="400" data-cursor="true" data-thickness="0.3" 
			data-fgColor="#5C9CCC" data-displayPrevious="true" data-min="0" data-max="3199" value="0"></center>
	</div>
	</div>
</div>



