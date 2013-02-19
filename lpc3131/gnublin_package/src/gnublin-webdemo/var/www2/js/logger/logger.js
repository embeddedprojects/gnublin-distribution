/*
	Logger
	by Anton Hammerschmidt - embedded projects GmbH

	Usage:
  Logger.Add('logging', 'Erstelle Container');
  Logger.Add('logging', 'Formatiere mit vFat');
  Logger.Add('logging', 'H&auml;nge neues Laufwerk ein');

  Logger.SetActive('logging', 1);
  Logger.SetDone('logging', 1);
*/

function Logger() { };

Logger.Add = function(id, value) {
	if(!($('#'+id+' ul').length > 0))
		$('#'+id).append('<ul></ul>');
		
	var count = $('#'+id+' ul li').size();
	count++;
			
	$('#'+id+' ul').append('<li class="grey" id="'+id+'-entry-'+count+'">'+value+'</li>');
}

Logger.Reset = function(id) {
	$('#'+id).html('');
}

Logger.SetActive = function(id, index) {
	$('#'+id+'-entry-'+index).removeClass('grey');
	$('#'+id+'-entry-'+index+' div').remove();
	var content = $('#'+id+'-entry-'+index).html();
	
	$('#'+id+'-entry-'+index).html(content + ' <div class="active"></div>');
}

Logger.SetDone = function(id, index) {
	$('#'+id+'-entry-'+index).removeClass('grey');
	$('#'+id+'-entry-'+index+' div').remove();
	var content = $('#'+id+'-entry-'+index).html();
	
	$('#'+id+'-entry-'+index).html(content + ' <div class="done"></div>');
}

Logger.SetFailed = function(id, index) {
	$('#'+id+'-entry-'+index).removeClass('grey');
	$('#'+id+'-entry-'+index+' div').remove();
	var content = $('#'+id+'-entry-'+index).html();
	
	$('#'+id+'-entry-'+index).html(content + ' <div class="failed"></div>');
}

