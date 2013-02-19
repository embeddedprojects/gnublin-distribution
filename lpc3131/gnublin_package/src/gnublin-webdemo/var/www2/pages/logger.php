<?php

class Logger 
{

  function Logger(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","LoggerList");
    $this->app->ActionHandler("data","LoggerData");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }


  function LoggerList()
  {
    $this->app->Tpl->Parse(PAGE,"logger_list.tpl");
  }

	function LoggerData()
	{
		$input = $_POST['input'];

		$data = array();
		for($i=0; $i<count($input); $i++) {
			$data[$i] = array('label'=>$input[$i],
												'value'=>$this->GetSensorData($input[$i]));

		}

		echo json_encode($data);
		exit;
	}

	function GetSensorData($sensor)
	{
		$temp = exec("/bin/bash /usr/bin/gnublin-lm75 -a 0x48", $result); 
		return preg_replace('/[^0-9]+/','',$temp);
	}

}
?>
