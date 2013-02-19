<?php


class User 
{

  function User(&$app)
  {
    $this->app = &$app;
  }

  function GetID()
  { 
    return $this->app->DB->Select("SELECT user_id FROM useronline WHERE sessionid='".session_id()."'
      AND ip='".$_SERVER[REMOTE_ADDR]."' AND login='1'");
  }

  function GetType()
  { 
    $type = $this->app->DB->Select("SELECT type FROM user WHERE id='".$this->GetID()."'");
    if($type=="")
      $type = $this->app->Conf->WFconf[defaultgroup];
    
    return $type;
  }
  
  function GetParameter($index)
  {
		$id = $this->GetID();
		
		if($index!="")
		{
			$settings = $this->app->DB->Select("SELECT settings FROM user WHERE id='$id' LIMIT 1");
      $settings = unserialize(base64_decode($settings));
			
			if(isset($settings[$index]))
				return $settings[$index];
		} 
  } 
 
  // value koennen beliebige Datentypen aus php sein (serialisiert) 
  function SetParameter($index,$value)
  {
		$id = $this->GetID();

		if($index!="" && isset($value))
		{
			$settings = $this->app->DB->Select("SELECT settings FROM user WHERE id='$id' LIMIT 1");
			$settings = unserialize(base64_decode($settings)); 

			$settings[$index] = $value;

			$settings = base64_encode(serialize($settings));
			$this->app->DB->Update("UPDATE user SET settings='$settings' WHERE id='$id' LIMIT 1");
		}
  	
  }

  function GetUsername()
  { 
    return $this->app->DB->Select("SELECT username FROM user WHERE id='".$this->GetID()."'");
  }

  function GetDescription()
  { 
    return $this->app->DB->Select("SELECT description FROM user WHERE id='".$this->GetID()."'");
  }

  function GetMail()
  { 
    return $this->app->DB->Select("SELECT email FROM adresse WHERE id='".$this->GetAdresse()."'");
  }


  function GetName()
  { 
    return $this->app->DB->Select("SELECT name FROM adresse WHERE id='".$this->GetAdresse()."'");
  }


  function GetAdresse()
  { 
    return $this->app->DB->Select("SELECT adresse FROM user WHERE id='".$this->GetID()."'");
  }

  function DefaultProjekt()
  {
    $adresse = $this->GetAdresse();
    $projekt = $this->app->DB->Select("SELECT projekt FROM adresse WHERE id='".$adresse."'");
    if($projekt <=0)
      $projekt = $this->app->DB->Select("SELECT standardprojekt FROM firma WHERE id='".$this->app->User->GetFirma()."' LIMIT 1");

    return $projekt;
  }

  function GetEmail()
  { 
    $adresse = $this->GetAdresse();
    return $this->app->DB->Select("SELECT email FROM adresse WHERE id='".$adresse."'");
  }


  function GetFirma()
  { 
    //return $this->app->DB->Select("SELECT firma FROM adresse WHERE id='".$this->GetAdresse()."'");
    return 1;
  }


  function GetFirmaName()
  { 
    return $this->app->DB->Select("SELECT name FROM firma WHERE id='".$this->GetFirma()."'");
  }


}
?>
