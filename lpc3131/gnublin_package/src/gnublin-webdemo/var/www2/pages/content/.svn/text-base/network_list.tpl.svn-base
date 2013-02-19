<link rel="stylesheet" type="text/css" href="modules/network/style.css">

<script type="text/javascript">
$(function() {
	$('#tabs').tabs();

	var locked = false;

	function AddLine(device) {
		var line = document.createElement('tr');
		$(line).addClass('device');
	
		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'interface'
		}).html(device.interface).addClass('lock'));

		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'type'
	  }).html(device.type));
	
		$(line).append($("<td></td>").attr({
      device: device.interface,
      option: 'dhcp'
		}).html(device.dhcp));

		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'address'
	  }).html(device.address));

		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'netmask'
	  }).html(device.netmask));

		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'gateway'
	  }).html(device.gateway));
	
		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'broadcast'
		}).html(device.broadcast));

		$(line).append($("<td></td>").attr({
			device: device.interface,
			option: 'nameserver'
		}).html(device.nameserver));
		
		$('table#network').append(line);
	}

		function Refresh() {
		$('table#network tr.device').remove();
		
		$.getJSON('index.php?module=network&action=refresh', function(data) { 
			$.each(data, function(index, device) { 
				AddLine(device);
			});
		});
	}

	function Send() {
		var lines = $('table#network tr.device');
	
		var devices = [];
		$(lines).each(function(index, line) {
			var fields = $(line).children('td');
			
			var device = {};
			$(fields).each(function(index, field) {
				
				var option = $(field).attr('option');
				var val = $(field).html();
			
				device[option] = val;
	
			});
			devices.push(device);
		});

		console.log(devices);

		$.get('index.php?module=network&action=set&data='+JSON.stringify(devices), function() { Refresh();
		});

	}


	function Click(el) {
		if($(el).children().length==0 && locked==false) {
			var inner = $(el).html();

			var input = document.createElement('input');
			$(input).attr({
				type: 'text',
				value: inner 
			});

			$(el).html(input);
			$(input).focus();
			locked = true;
		}
	}

	function Blur(el) {
		var val = $(el).attr('value');
		var input = $(el).children('input[type="text"]');

		$(el).html($(input).val());
		$(input).remove();
		locked = false;
	 	Send();	
	}

	function init() {
		Refresh();
	
		$('table#network tr.device td:not(.lock)').live('click', function() {
			Click(this);
		});

		$('table#network tr.device td:not(.lock)').live('blur', function() {
	    Blur(this);
	  });

	}


	init();

});
</script>

<div id="tabs">
	<ul><li><a href="#tabs-1">Netwerkadresse</a></li></ul>
	<div id="tabs-1">
	<table id="network">
		<tr class="head">
			<td>Interface</td>
			<td>IPv4/v6</td>
			<td>Typ</td>
			<td>IP-Adresse</td>
			<td>Subnetzmaske</td>
			<td>Gateway</td>
			<td>Broadcast</td>
			<td>DNS-Server</td>
		</tr>
	</table>
	</div>
</div>



