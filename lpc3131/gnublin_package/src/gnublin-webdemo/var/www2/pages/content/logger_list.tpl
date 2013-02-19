<link rel="stylesheet" type="text/css" href="modules/logger/style.css">
<link rel="stylesheet" type="text/css" href="modules/logger/button.css">


<script>
$(function() {
	$( "#tabs" ).tabs();
 	$( "#from" ).datepicker();
 	$( "#to" ).datepicker();
});
</script>

<script type="text/javascript">
$(function() {
	var active = new Array();
	var update_interval = 200;


	var options = {
		series: { shadowSize: 0 },
		xaxis: { show: false }
	};

	function init() {

		$('.sensor').each(function() {
			CheckSensorStatus(this);
		});

		$('.sensor').change(function() {	
			CheckSensorStatus(this);
		});

		$('#send').click(function() {
			var sensors = $('.sensor');
			var interval = $('#interval').val();
			var period = $('#period').val();
			var from = $('#from').val();
			var to = $('#to').val();
		});

		$('#interval').change(function() {
			var v = $(this).val();
			if (v && !isNaN(v)) {
				update_interval = v;
				if (update_interval < 1)
					update_interval = 200;
			}
		});

		getData();
	}

	function CheckSensorStatus(cb) {
		var name = $(cb).attr('id');
		var checked = $(cb).attr("checked");

		if(checked) 
			active[name] = [];
		else
		  delete active[name];
	}

	function DrawGraph() {
		var graph = [];
		for(var channel in active) {
			var current_data = [];

			var coords = active[channel];
			var current_data = [];
			for(var i=0; i < coords.length; i++) {
				current_data.push([i, coords[i]]);
			}
			
			var current_graph = { label: channel, data: current_data};
			graph.push(current_graph);
		}

		$.plot($("#logger"), graph, options);	
	}

	function AddToChannel(channel, value) {
		var channel_data = active[channel];
		if(channel_data.length>100) channel_data.shift();
			channel_data.push(parseFloat(value));
		active[channel] = channel_data;
	}

	function getData() {
		DrawGraph();
		var active_out = [];

		// Convert Array
		for(var a in active) 
			active_out.push(a);

		$.post('index.php?module=logger&action=data', {'input': active_out }, function(data){
			data = jQuery.parseJSON(data);

			$.each(data, function(i, channel) {
				AddToChannel(channel.label, channel.value);			
			});
			
		});
		setTimeout(getData, update_interval);
	}


	init();

/*
var data= [[0,0],[600,300]];
var options = {
	series: { shadowSize: 0 }, // drawing is faster without shadows
	yaxis: { min: -1, max: 1 },
	xaxis: { show: false }
};

$.plot("#logger", data, options);
*/
});
</script>


<div id="tabs">
	<ul><li><a href="#tabs-1">Datenlogger</a></li></ul>
	<div id="tabs-1">

	<div id="logger"></div>
		
	<ul id="sensorlist">
		<li><input type="checkbox" class="sensor" id="sensor1" value="1" checked><label for="sensor1">Innentemperatur</label></li>
		<li><input type="checkbox" class="sensor" id="sensor2" value="1"><label for="sensor2">Außentemperatur</label></li>
		<li><input type="checkbox" class="sensor" id="sensor3" value="1"><label for="sensor3">Wassertemperatur</label></li>
		<li style="margin-top: 20px">Intervall <input type="text" id="interval" value="200"></li>
		<li>Daten <select id="period"><option value="day">Täglich</option><option value="month">Monatlich</option><option value="year">Jährlich</option></select></li>
		<li>Von <input type="text" id="from"></li>
		<li>Bis <input type="text" id="to"></li>
		<li style="text-align:right;"><input type="button" class="btn" id="send" value="OK"></li>
	</ul>
	<div style="clear:left"></div>
	</div>

</div>



