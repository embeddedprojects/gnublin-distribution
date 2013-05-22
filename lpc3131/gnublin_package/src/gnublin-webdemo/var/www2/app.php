<?
/* Author: Benedikt Sauter <sauter@sistecs.de> 2007
 *
 * Hier werden alle Plugins, Widgets usw instanziert die
 * fuer die Anwendung benoetigt werden.
 * Diese Klasse ist von class.application.php abgleitet.
 * Das hat den Vorteil, dass man dort bereits einiges starten kann,
 * was man eh in jeder Anwendung braucht.
 * - DB Verbindung
 * - Template Parser
 * - Sicherheitsmodul
 * - String Plugin
 * - usw....
 */
require("./phpwf/class.application.php");
include("./lib/core.php");
/*
require("lib/class.erpapi.php");
require("lib/class.printer.php");
require("plugins/phpmailer/class.phpmailer.php");
  
require("lib/class.httpclient.php");
require("lib/class.aes.php");
require("lib/class.remote.php");
require("lib/class.help.php");

include("/opt/fred/drivers/lua.php");
include("/opt/fred/drivers/fredcontrol.php");
*/

class myApp extends Application
{
  public $obj;
  public $starttime;
  public $endtime;
  public $fredcontrol;

  public function __construct($config,$group="") 
  {
    $this->starttime = microtime(); 
    parent::Application($config,$group);

  	// $this->fredcontrol = new FredControl();
  	// $this->lua = new LUA();

		$this->Core = new Core(&$this);

		$this->Tpl->ReadTemplatesFromPath("./pages/content/");
		$this->Tpl->Set(THEME,$this->Conf->WFconf[defaulttheme]);

  }


  function calledWhenAuth($type)
  {

  } 
}





?>
