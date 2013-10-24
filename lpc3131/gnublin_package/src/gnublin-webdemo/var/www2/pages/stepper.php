<?php

class Stepper 
{

  function Stepper(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","StepperList");
    $this->app->ActionHandler("set","StepperSet");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function StepperList()
  {
    $this->app->Tpl->Parse(PAGE,"stepper_list.tpl");
  }

	function StepperSet()
	{
		$value = $_GET['value'];

		if($value >= 0 && $value <= 3199)
			exec("/usr/bin/gnublin-step -p $value");

		exit;
	}
}
?>
