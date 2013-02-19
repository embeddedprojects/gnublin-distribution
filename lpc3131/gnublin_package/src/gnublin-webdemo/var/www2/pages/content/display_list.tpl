<script type="text/javascript" src="modules/stepper/jquery.knob.js"></script>


<script type="text/javascript">
$(function() {
	$('#tabs').tabs();

	var curText = '';
	var valid = new Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',
												'S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9','?');
	$(document).keydown(function(e) {
		if(e.keyCode==13){
			$.get('index.php?module=display&action=set&text='+curText);
	}
		var k = String.fromCharCode(e.keyCode);
		curText = $('#display').val();

		if(curText.length <= 32 || e.keyCode == 8) {

			if(e.keyCode == 8 && curText.length > 0)
				curText = curText.substr(0, curText.length-1);
			else if(valid.indexOf(k) > -1){
				curText += k;
				if(curText.length == 16) curText += "\n";
			}

			$('#display').val(curText);
		}
	});
});
</script>

<style type="text/css">
@font-face { font-family: Lcd; src: url(modules/display/LCD2.ttf); } 

textarea#display {
	resize: none;
	overflow:hidden;
	font: 90px Lcd;
	line-height: 90px;
	padding: 10px 5px 10px 20px;
	border: 14px solid #334342;
	border-radius: 15px;
	background: url(modules/display/lcd-bg.png) repeat;
	color: #193c04;
	text-transform: uppercase;
	height: 165px;
	letter-spacing: 0.2em;
	margin: 20px;
}


</style>


<div id="tabs">
	<ul><li><a href="#tabs-1">Display</a></li></ul>
	<div id="tabs-1">
	
	<center><textarea id="display" rows="2" cols="16" maxlength="32" disabled></textarea></center>
	
	</div>
</div>



