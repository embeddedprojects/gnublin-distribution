<?php

class Configuration
{

  function Configuration(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","ConfigurationList");
    $this->app->ActionHandler("doc","ConfigurationDoc");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function ConfigurationList()
  {
    $this->app->Tpl->Parse(PAGE,"configuration_list.tpl");
  }

}
?>
