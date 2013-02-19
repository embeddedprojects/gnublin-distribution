<?php

class Draft 
{

  function Draft(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","DraftList");
    $this->app->ActionHandler("doc","DraftDoc");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function DraftList()
  {
    $this->app->Tpl->Parse(PAGE,"draft_list.tpl");
  }

}
?>
