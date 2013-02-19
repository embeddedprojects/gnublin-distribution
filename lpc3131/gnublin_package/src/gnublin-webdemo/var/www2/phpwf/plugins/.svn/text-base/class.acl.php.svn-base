<?php


class Acl 
{
  //var $engine;
  function Acl(&$app)
  {
    $this->app = &$app;
  }


  function CheckTimeOut()
  {
    // check if user is applied 
    $sessid =  $this->app->DB->Select("SELECT sessionid FROM useronline,user WHERE
       login='1' AND sessionid='".session_id()."' AND user.id=useronline.user_id AND user.activ='1' LIMIT 1");
    
    if(session_id() == $sessid)
    { 
      // check if time is expired
      $time =  $this->app->DB->Select("SELECT UNIX_TIMESTAMP(time) FROM useronline,user WHERE
       login='1' AND sessionid='".session_id()."' AND user.id=useronline.user_id AND user.activ='1' LIMIT 1");

      if((time()-$time) > $this->app->Conf->WFconf[logintimeout])
      {
	//$this->app->WF->ReBuildPageFrame();
	$this->Logout("Ihre Zeit ist abgelaufen, bitte melden Sie sich erneut an.");
	return false;
      }
      else {
	// update time
	 $this->app->DB->Update("UPDATE useronline,user SET useronline.time=NOW() WHERE
            login='1' AND sessionid='".session_id()."' AND user.id=useronline.user_id AND user.activ='1'");
            
         session_write_close(); // Blockade wegnehmen           
                
	return true; 
      }
    }

  }

  function Check($usertype,$module,$action)
  {
    $ret = false;
    $permissions = $this->app->Conf->WFconf[permissions][$usertype][$module];
    
    while (list($key, $val) = @each($permissions)) 
    {
      if($val==$action || $usertype=="admin")
      {
	$ret = true;
	break;
      }
    }
    
    if(!$ret)
    {
      $this->app->Tpl->Parse(PAGE,"permissiondenied.tpl");
    }
    return $ret;
  }

  function Login()
  {
    $username = $this->app->Secure->GetPOST("username");
    $password = $this->app->Secure->GetPOST("password");
  
    if($username=="" && $password==""){
      $this->app->Tpl->Set(LOGINMSG,"Bitte geben Sie Benutzername und Passwort ein.");  
      $this->app->Tpl->Parse(PAGE,"login.tpl");
    }
    elseif($username==""||$password==""){
      $this->app->Tpl->Set(LOGINERRORMSG,"Bitte geben Sie einen Benutzername und ein Passwort an.");  
      $this->app->Tpl->Parse(PAGE,"login.tpl");
    }
    else {
      $encrypted = $this->app->DB->Select("SELECT password FROM user
        WHERE username='".$username."' AND activ='1' LIMIT 1");

      $fehllogins= $this->app->DB->Select("SELECT fehllogins FROM user
        WHERE username='".$username."' AND activ='1' LIMIT 1");

      $type= $this->app->DB->Select("SELECT type FROM user
        WHERE username='".$username."' AND activ='1' LIMIT 1");



      $password = substr($password, 0, 8);
       

      if (crypt( $password,  $encrypted ) == $encrypted  && $fehllogins<6)
      { 
        $user_id = $this->app->DB->Select("SELECT id FROM user
          WHERE username='".$username."' AND activ='1' LIMIT 1");
      }
      else { $user_id = ""; }

      if(is_numeric($user_id))
      { 
        $this->app->DB->Insert("INSERT INTO useronline (user_id, sessionid, ip, login, time)
          VALUES ('".$user_id."','".session_id()."','".$_SERVER[REMOTE_ADDR]."','1',NOW())");

	$this->app->DB->Select("UPDATE user SET fehllogins=0
        WHERE username='".$username."' LIMIT 1");

				$this->app->erp->calledOnceAfterLogin($type);

				$startseite = $this->app->DB->Select("SELECT startseite FROM user WHERE id='$user_id' LIMIT 1");
				if($startseite!="")
        header("Location: ".$this->app->http."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$startseite);
				else
        header("Location: ".$this->app->http."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
	exit;
      }
      else if ($fehllogins>=6)
      {

	$this->app->Tpl->Set(LOGINERRORMSG,"Es gibt technische Probleme. Bitte wenden Sie sich an Ihren Vorgesetzten.");  
	$this->app->Tpl->Parse(PAGE,"login.tpl");

      }
      else
      { 

       $this->app->DB->Select("UPDATE user SET fehllogins=fehllogins+1 WHERE username='".$username."' LIMIT 1");

	$this->app->Tpl->Set(LOGINERRORMSG,"Benutzername oder Passwort falsch.");  
	$this->app->Tpl->Parse(PAGE,"login.tpl");
      }
    }
  }

  function Logout($msg="")
  {
    $username = $this->app->User->GetName();
    $this->app->DB->Delete("UPDATE useronline SET login='0' WHERE user_id='".$this->app->User->GetID()."'");
    session_destroy();
    session_start();
    session_regenerate_id(true);
    $_SESSION['database']="";
    header("Location: ".$this->app->http."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    exit;
    $this->app->Tpl->Set(LOGINERRORMSG,$msg);  
    $this->app->Tpl->Parse(PAGE,"login.tpl");
  }


  function CreateAclDB()
  {

  }

}
?>
