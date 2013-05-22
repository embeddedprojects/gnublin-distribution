<?php

class Gpio 
{

  function Gpio(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","GpioList");
    $this->app->ActionHandler("status","GpioStatus");
    $this->app->ActionHandler("set","GpioSet");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function GpioList()
  {
    $this->app->Tpl->Parse(PAGE,"gpio_list.tpl");
  }

	function GpioStatus()
	{
		$gpio = $_GET['gpio'];

		if(!is_numeric($gpio))
			exit;

		exec("/bin/bash gnublin-gpio -b -p $gpio -i", $result);
		echo $result[0];

		exit;
	}

	function GpioSet()
	{
		$gpio = $_GET['gpio'];
	
		exec("/bin/bash gnublin-gpio -b -p $gpio -i", $result);

		$set = 0;
		if($result[0]=='0')
			$set = 1;

		exec("/bin/bash gnublin-gpio -b -p $gpio -o $set");

		echo $set;
		exit;
	}
}
?>
