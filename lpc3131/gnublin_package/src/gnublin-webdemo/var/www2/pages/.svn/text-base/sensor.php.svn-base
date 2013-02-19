<?php

class Sensor 
{

  function Sensor(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","SensorList");
    $this->app->ActionHandler("doc","SensorDoc");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function SensorList()
  {
    $this->app->Tpl->Parse(PAGE,"sensor_list.tpl");
  }

}
?>
