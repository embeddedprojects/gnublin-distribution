<?php

class Welcome 
{

  function Welcome(&$app)
  {
    $this->app=&$app; 
  
    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("start","WelcomeStart");
    $this->app->ActionHandler("info","WelcomeInfo");
  
    $this->app->DefaultActionHandler("start");

    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);
  }



  function WelcomeStart()
  {
    $this->app->Tpl->Set(UEBERSCHRIFT,"Ihre Startseite");

/*
    $curfree = $this->app->lua->SingleCommand("avr_cur_free_ram()");
    $this->app->Tpl->Set(CURFREE,$curfree);

    $minfree = $this->app->lua->SingleCommand("avr_min_free_ram()");
    $this->app->Tpl->Set(MINFREE,$minfree);
*/
 //   $ramused = exec("free -m -s 1 | awk '/Mem/{print $3}'");
    $this->app->Tpl->Set(RAMUSED,$ramused);
    

    $this->app->Tpl->Parse(PAGE,"welcome_list.tpl");
  }

  function WelcomeInfo()
  {

    $this->app->Tpl->Set(KURZUEBERSCHRIFT,"Ihre Startseite");
    $this->app->Tpl->Set(KURZUEBERSCHRIFT2,"[BENUTZER]");

    $this->app->Tpl->Set(TABTEXT,"Info");
    $this->app->Tpl->Set(TAB1,"Infotext");
    $this->app->Tpl->Parse(PAGE,"tabview.tpl");
  }
}
?>
