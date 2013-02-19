<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>GNUBLIN Web Demo</title>
    <link rel="stylesheet" type="text/css" href="themes/[THEME]/css/redmond/jquery-ui-1.9.2.custom.min.css" />
    <link rel="stylesheet" type="text/css" href="themes/[THEME]/css/main.css" />
    <link rel="stylesheet" type="text/css" href="js/modalPopup/css/style.css" />


    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="themes/[THEME]/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="js/modalPopup/js/jquery.reveal.js"></script>

    <script language="javascript" type="text/javascript" src="./js/jquery.flot.js"></script>
  </head>


 <script>
$(function() {
$( "#menu" ).menu();
});
</script>


<body>
<table border="0">
	<tr valign="top"><td width="180"><img src="./themes/[THEME]/images/logo.jpg"></td><td></td></tr>
	<tr valign="top"><td>
		<ul id="menu">
			<li><a href="index.php?module=welcome&action=start">Home</a></li>
			<li><a href="index.php?module=gpio&action=list">GPIO Demo</a></li>
			<li><a href="index.php?module=stepper&action=list">Schrittmotor</a></li>
			<li><a href="index.php?module=display&action=list">Display</a></li>
			<li><a href="index.php?module=logger&action=list">Datenlogger</a></li>
			<li><a href="index.php?module=network&action=list">Netzwerkadresse</a></li>
		</ul>
</td><td>
	[PAGE]
</td></tr>
</table>
</body>
</html>
