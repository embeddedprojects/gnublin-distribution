<?
define("COMMON",0);
define("GUI",1);
define("MP3",2);
define("SAFE",3);
define("OTP",4);

class AppList
{
  var $ListEntries;
  function AppList()
  {
    $this->ListEntries = array();
    $this->Murmel("123", MP3,"2012-03-01 17:10","2012-03-28 20:30"); //murmel fuer mp3
    $this->Murmel("4789",SAFE); //murmel fuer mp3
  }


  function GetAppName($appid)
  {
    switch($appid)
    {
      case COMMON:  return "common"; break;
      case GUI:     return "gui"; break;
      case MP3:     return "mp3"; break;
      case SAFE:    return "safe"; break;
      case OTP:     return "otp"; break;
    }
  }

  function GetAppFromMurmel($murmel)
  {
    while(strlen($murmel)>0)
    {
      if(array_key_exists ( $murmel , $this->ListEntries))
      {
        return $this->ListEntries[$murmel][0];
      } 
      $murmel = substr($murmel, 1);
    }
    return -1;

  }

  function Murmel($murmel,$appid,$start="",$end="")
  {
    $this->ListEntries[$murmel] = array($appid,$start,$end);
  }

}
?>
