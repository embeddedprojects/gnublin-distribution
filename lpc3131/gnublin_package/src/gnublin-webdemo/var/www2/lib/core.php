<?
class Core
{
	var $app;

	function Core($app)
	{
		$this->app = &$app;
		$this->app = $app;
	}

	function RandomString($len)
	{
		mt_srand((double) microtime() * 1000000); 
		 
		$set = "ABCDEFGHIKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789";
		$str = "";
		  
		for ($n=1;$n<=$len;$n++)
			$str .= $set[mt_rand(0,(strlen($set)-1))];

		return $str;
	}

	function MailSend($from,$from_name,$to,$to_name,$betreff,$text,$files="")
	{
		$this->app->mail->ClearData();
		$this->app->mail->From       = $from;
		$this->app->mail->FromName   = utf8_decode($from_name);

		$this->app->mail->Subject    = utf8_decode($betreff);
		$this->app->mail->AddAddress($to, utf8_decode($to_name));

		$this->app->mail->Body = utf8_decode(str_replace('\r\n',"\n",$text).$this->Signatur());

		//$this->app->mail->AddBCC('sauter@ixbat.de');
		//$this->app->mail->AddBCC('claudia.sauter@embedded-projects.net');
		//$this->app->mail->AddBCC('sauter@embedded-projects.net');

		for($i=0;$i<count($files);$i++)
			$this->app->mail->AddAttachment($files[$i]);

		if(!$this->app->mail->Send()) {
			$error =  "Mailer Error: " . $this->app->mail->ErrorInfo;
			return 0;
		} else {
			$error = "Message sent!";
			return 1;
		}
	}

	function Signatur()
	{
		return "

--

embedded projects GmbH
Holzbachstraße 4
D-86152 Augsburg

Tel +49 821 2795990
Fax +49 821 27959920
		
Name der Gesellschaft: embedded projects GmbH
Sitz der Gesellschaft: Augsburg

Handelsregister: Augsburg, HRB 23930
Geschäftsführung: Benedikt Sauter, Dipl.-Inf.(FH)
USt-IdNr.: DE263136143

AGB: http://www.eproo.net/";
	}

  function Captcha()
  {
    // Text erzeugen
    $str = "";
    $length = 0;
    for ($i = 0; $i < 4; $i++)
      $str .= chr(rand(97, 122));

		setcookie("wawision_captcha", $str);
    
		// Dimensionen
    $imgX = 80;
    $imgY = 35;
    $image = imagecreatetruecolor($imgX, $imgY);

    // Farben
    $rgb1 = rand(0, 255);
    $rgb2 = rand(0, 255);
    $rgb3 = rand(0, 255);

    // Bild füllen
   	 $backgr_col = imagecolorallocate($image, $rgb1, $rgb2, $rgb3);
   	 $border_col = imagecolorallocate($image, 208,208,208);
   	 $text_col = imagecolorallocate($image, ($rgb1 - 50), ($rgb2 - 50), ($rgb3 - 50));

    imagefilledrectangle($image, 0, 0, $imgX, $imgY, $backgr_col);
    imagerectangle($image, 0, 0, $imgX-1, $imgY-1, $border_col);

    $font = "./pages/fonts/VeraSe.ttf";
    $font_size = 15;
    $angleMax = 20;
    $angle = rand(-$angleMax, $angleMax);
    $box = imagettfbbox($font_size, $angle, $font, $str);
    $x = (int)($imgX - $box[4]) / rand(1.8,2.2);
    $y = (int)($imgY - $box[5]) / 2;
    imagettftext($image, $font_size, $angle, $x, $y, $text_col, $font, $str);

    // Bild schicken
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy ($image);
  }

	// Convertiert MySQL-Datum YYYY-MM-DD nach DD.MM.YYYY
  function ConvertDate($mysqlDate)
  {
    if($mysqlDate != "")
      return $this->Convert($mysqlDate,"%1-%2-%3","%3.%2.%1");
  }

  // Convertiert nach MySQL-Datum, DD.MM.YYYY nach YYYY-MM-DD 
  function ConvertToSqlDate($date)
  {
    if($date != "")
      return $this->Convert($date,"%3.%2.%1","%1-%2-%3");
  }

	function Convert($value,$input,$output)
  {
    if($input=="")
      return $value;

    $array = $this->FindPercentValues($input);
    $regexp = $this->BuildRegExp($array);

    $elements =
      preg_split($regexp,$value,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    // input und elements stimmmen ueberein

    $newout = $output;
    $i = 0;
    foreach($array as $key=>$value)
    {
      $newout = str_replace($key,$elements[$i],$newout);
      $i++;
    }
    return $newout;
  }

  function BuildRegExp($array)
  {

    $regexp = '/^';
    foreach($array as $value)
    {
      $value = str_replace('.','\.',$value);
      $value = str_replace('+','\+',$value);
      $value = str_replace('*','\*',$value);
      $value = str_replace('?','\?',$value);
      $regexp .= '(\S+)'.$value;
    }
    $regexp .= '/';

    return $regexp;
  }

	function FindPercentValues($pattern)
  {
    preg_match_all('/(?:(%[0-9]+)|.)/i', $pattern, $matches);

    $start = true;
    foreach($matches[1] as $key=>$value)
    {
      if($value=="")
  			$collecting = true;
      else
      {
  			$collecting = false;
  			$oldhash = $hash;
  			$hash = $value;
      }

      if(!$collecting)
      {
  			if(!$start)
    			$replace[$oldhash] = $collect;
  			$collect="";
      }
      else
  			$collect .=$matches[0][$key];
      $start = false;
    }
    $replace[$hash] = $collect;
    return $replace;
  }

	function configAdminAccess()
	{
	 	$docRoot = $_SERVER['DOCUMENT_ROOT'];

		//Suche in derzeitiger htaccess nach document_root
		$found = false;

		if(file_exists("{$docRoot}/admin/.htaccess"))
		{
			$content = file_get_contents("{$docRoot}/admin/.htaccess");
			$pos = stripos($content, $docRoot);
			if(is_numeric($pos))
				$found = true;
		}

		// schreibe neue .htaccess
		if($found==false)
		{
			$tpl = file_get_contents("{$docRoot}/conf/htaccess.tpl");
			$tpl = str_replace("[DOCROOT]", $docRoot."/admin/", $tpl);

			$file = fopen("{$docRoot}/admin/.htaccess","w");
			fwrite($file, $tpl);
			fclose($file);
		}
	}

	function BuildSidebar($sidebar)
	{
		foreach($sidebar AS $key=>$value)
			$entries .= "<li><a href=\"$value\">$key</a></li>";

		$out = '<div id="sidebar" class="column-left"><ul>
							<li><h4>[SIDEBAR_TITLE]</h4><ul>'.$entries.'</ul></li>
						</ul></div>';

		$this->app->Tpl->Set('SIDEBAR', $out);
	}

}
?>
