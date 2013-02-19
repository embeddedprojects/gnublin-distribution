<?
    
//database connection
class Config {
    
  function Config() 
  {
    //session_start();
//    include("user.inc.php");
    
    // define defaults
    $this->WFconf[defaultpage] = 'welcome';
    $this->WFconf[defaultpageaction] = 'start';
    $this->WFconf[defaulttheme] = 'gnublin-web';
    $this->WFconf[defaultgroup] = 'web';
    
    // allow that cols where dynamically added so structure
    $this->WFconf[autoDBupgrade]=true;
    
    // time how long a user can be connected in seconds genau 8 stunden
    $this->WFconf[logintimeout] = 3600 * 4;
    
    // alle vorhanden Gruppen in diesem System
    $this->WFconf[groups] = array('web','admin');
    
    // gruppen die sich anmelden muessen
    $this->WFconf[havetoauth] = array('admin');
    
    //menu structure
    
    // public menu
    $this->WFconf[menu][web][0][first]  = array('wawision','welcome','main');
    $this->WFconf[menu][web][0][sec][]  = array('Anmelden','welcome','login');
    //$this->WFconf[menu][web][0][sec][] = array('Hilfe','welcome','help'); 
 
    //$this->WFconf[permissions][admin][projekt] = array('list','create','edit','delete','arbeitspaket','arbeitspaketeditpopup','dateien','kostenstellen','schaltung','zeit','material');


  }
}
?>
