<?php

class Report 
{

  function Report(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","ReportList");
    $this->app->ActionHandler("doc","ReportDoc");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function ReportList()
  {
    $this->app->Tpl->Parse(PAGE,"report_list.tpl");
  }

}
?>
