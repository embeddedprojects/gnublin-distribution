 <script>
$(function() {
$( "#tabs" ).tabs();

 $( "#selectable" ).selectable({
stop: function() {
var result = $( "#select-result" ).empty();
$( ".ui-selected", this ).each(function() {
var index = $( "#selectable li" ).index( this );
result.append( " #" + ( index + 1 ) );
});
}
});

});
</script>

 <style>
#feedback { font-size: 1.4em; }
#selectable .ui-selecting { background: #FECA40; }
#selectable .ui-selected { background: #F39814; color: white; }
#selectable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable li { margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }
</style>

<div id="tabs">
<ul>
<li><a href="#tabs-1">Sensoren</a></li>
</ul>
<div id="tabs-1">


<table>
<tr valign="top"><td width="300">

<p id="feedback">
<span>You've selected:</span> <span id="select-result">none</span>.
</p>
<ol id="selectable">
<li class="ui-widget-content">Sensor Rücklauf</li>
<li class="ui-widget-content">Stromz&auml;hler 1</li>
<li class="ui-widget-content">Warmwasser</li>
<li class="ui-widget-content">Au&szlig;entemperatur</li>
</ol>

</td><td>
<br><br>
<br><br>
<table>
<tr><td width="100">Name:</td><td><input type="text" size="20" value="Sensor Rücklauf"></td></tr>
<tr><td>aktiv</td><td><input type="checkbox" value="1" checked></td></tr>
<tr><td width="100">Sensortyp:</td><td><select><option>Spannung 0 - 10V</option><option>Strom 0 - 20 mA</option>
		<option>S0-Eingang</option>
		<option>Areus Funkmodul</option>
		</select></td></tr>

<tr><td width="100">Intervall:</td><td><select><option>zyklisch</option><option>t&auml;glich</option>
			<option>w&ouml;chentlich</option>
			<option>monatlich</option>
		</td></tr>

<tr><td width="100">Farbe:</td><td><input type="text"></td></tr>

</table>


</td></tr>
</table>


</div>

</div>



