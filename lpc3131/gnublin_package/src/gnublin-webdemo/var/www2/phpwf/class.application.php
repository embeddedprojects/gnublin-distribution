<?
include("class.secure.php");
include("class.ini.php");
include("plugins/class.templateparser.php");

class Application
{

    var $ActionHandlerList;
    var $ActionHandlerDefault;

    function Application($config,$group="")
    {
      session_cache_limiter('private');
//      session_start();
      $this->Conf= $config;
/*
      if($_SERVER[HTTPS]=="on")
        $this->http = "https";
      else
        $this->http = "http";
*/
      $this->Secure         = & new Secure($this);   // empty $_GET, and $_POST so you
                                                // have to need the secure layer always
      $this->Tpl            = & new TemplateParser($this);
     /* 
      $this->FormHandler    = & new FormHandler($this);
      $this->User           = & new User($this);
      $this->YUI            = & new YUI($this);
      $this->acl            = & new Acl($this);
      $this->WF             = & new phpWFAPI($this);
      $this->WFM            = & new WFMonitor($this);
      $this->Page           = & new Page($this);
      $this->String         = & new String();
      $this->DatabaseForm   = & new DatabaseForm($this);
      $this->PageBuilder    = & new PageBuilder($this);
      $this->ObjAPI         = & new ObjectAPI($this);
      $this->Widget         = & new WidgetAPI($this);
      $this->Table          = & new Table($this);
*/
      $this->BuildNavigation = true;

      //$this->DB             = new DB($this->Conf->WFdbhost,$this->Conf->WFdbname,$this->Conf->WFdbuser,$this->Conf->WFdbpass,$this);

      $this->Tpl->ReadTemplatesFromPath("./themes/".$this->Conf->WFconf[defaulttheme]."/templates/");

    }

    function ActionHandlerInit(&$caller)
    {
      $this->caller = &$caller;
    }


    function ActionHandler($command,$function)
    {
      $this->ActionHandlerList[$command]=$function;
    }

    function DefaultActionHandler($command)
    {
      $this->ActionHandlerDefault=$command;
    }

    function ActionHandlerListen(&$app)
    {
      $action = $app->Secure->GetGET("action","alpha");
      if($action!="")
        $fkt = $this->ActionHandlerList[$action];
      else
        $fkt = $this->ActionHandlerList[$this->ActionHandlerDefault];


      // check permissions
      @$this->caller->$fkt();
    }
}


?>
