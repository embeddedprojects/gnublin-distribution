<?php

/// Secure Layer, SQL Inject. Check, Syntax Check
class Secure 
{
  var $GET;
  var $POST;

  function Secure()
  {
    // clear global variables, that everybody have to go over secure layer
    
    $this->GET = $_GET;
    $_GET="";
    $this->POST = $_POST;
    $_POST="";

    $this->AddRule('notempty','reg','.'); // at least one sign
    $this->AddRule('alpha','reg','[a-zA-Z]');
    $this->AddRule('digit','reg','[0-9]');
    $this->AddRule('space','reg','[ ]');
    $this->AddRule('specialchars','reg','[_-]');
    $this->AddRule('email','reg','^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$');
    $this->AddRule('datum','reg','([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})');
    
    $this->AddRule('username','glue','alpha+digit');
    $this->AddRule('password','glue','alpha+digit+specialchars');
  }
 

  function GetGET($name,$rule="",$maxlength="",$sqlcheckoff="")
  {
    return $this->Syntax($this->GET[$name],$rule,$maxlength,$sqlcheckoff);
  }

  function GetPOST($name,$rule="",$maxlength="",$sqlcheckoff="")
  {
    return $this->Syntax($this->POST[$name],$rule,$maxlength,$sqlcheckoff);
  }

  function GetPOSTArray()
  {
    if(count($this->POST)>0)
    {
      foreach($this->POST as $key=>$value)
      {
	$key = $this->GetPOST($key,"alpha+digit+specialchars",20);
	$ret[$key]=$this->GetPOST($value);
      }	
    }
    return $ret;
  }

  function GetGETArray()
  {
    if(count($this->GET)>0)
    {
      foreach($this->GET as $key=>$value)
      {
	$key = $this->GetGET($key,"alpha+digit+specialchars",20);
	$ret[$key]=$this->GetGET($value);
      }	
    }
    return $ret;
  }

function stripallslashes($string) {
    
    while(strchr($string,'\\')) {
        $string = stripslashes($string);
    }
  return $string;
} 

  function smartstripslashes($str) {
  $cd1 = substr_count($str, "\"");
  $cd2 = substr_count($str, "\\\"");
  $cs1 = substr_count($str, "'");
  $cs2 = substr_count($str, "\\'");
  $tmp = strtr($str, array("\\\"" => "", "\\'" => ""));
  $cb1 = substr_count($tmp, "\\");
  $cb2 = substr_count($tmp, "\\\\");
  if ($cd1 == $cd2 && $cs1 == $cs2 && $cb1 == 2 * $cb2) {
    return strtr($str, array("\\\"" => "\"", "\\'" => "'", "\\\\" => "\\"));
  }
  return $str;
  }

  // check actual value with given rule
  function Syntax($value,$rule,$maxlength="",$sqlcheckoff="")
  {
    if(is_array($value))
      return $value;
    $value = $this->stripallslashes($value);
    $value = $this->smartstripslashes($value);
    //$value = htmlspecialchars($value,ENT_QUOTES);
    //$value = html_entity_decode ($value);
		$value = str_replace('"','&Prime;',$value);
		$value = str_replace("'",'&prime;',$value);
		

//    $value = utf8_decode($value);
 
    //$value = strip_tags($value); //entfernt alle Entfernt HTML- und PHP-Tags aus einem String
    $value=strip_tags($value,'<ol><ul><li><h1><h2><h3><h4><h5><h6><em><br><p><strong><a><hr><span><pre>');

    if($maxlength!=""){
      if(strlen($value)>$maxlength)
        return "";
    }

    if($rule=="")
      return mysql_real_escape_string($value);

    // build complete regexp

    // check if rule exists
   
    if($this->GetRegexp($rule)!=""){
      //$v = '/^['.$this->GetRegexp($rule).']+$/';
      $v = $this->GetRegexp($rule);
      if (eregi($v, $value) )
      {
	if($sqlcheckoff=="")
	  return mysql_real_escape_string($value);
	else
	  return $value;
      }
      else
	return "";
    }
    else
    {
      echo "<table border=\"1\" width=\"100%\" bgcolor=\"#FFB6C1\">
	<tr><td>Rule <b>$rule</b> doesn't exists!</td></tr></table>";
      return "";
    }
  }


  function RuleCheck($value,$rule)
  {
    $v = $this->GetRegexp($rule);
    if (eregi($v, $value) )
      return true;
    else
      return false;
  }

  function AddRule($name,$type,$rule)
  {
    // type: reg = regular expression
    // type: glue ( already exists rules copy to new e.g. number+digit)
    $this->rules[$name]=array('type'=>$type,'rule'=>$rule);
  }

  // get complete regexp by rule name
  function GetRegexp($rule)
  {
    $rules = split("\+",$rule);

    foreach($rules as $key)
    {
        // check if rule is last in glue string
        if($this->rules[$key][type]=="glue")
        {
          $subrules = split("\+",$this->rules[$key][rule]);
          if(count($subrules)>0)
          {
            foreach($subrules as $subkey)
            {
              $ret .= $this->GetRegexp($subkey);
            }
          }
        }
        elseif($this->rules[$key][type]=="reg")
        {
          $ret .= $this->rules[$key][rule];
        }
        else
        {
          //error
        }
    }
    if($ret=="")
      $ret = "none";
    return $ret;
  }

}


?>
