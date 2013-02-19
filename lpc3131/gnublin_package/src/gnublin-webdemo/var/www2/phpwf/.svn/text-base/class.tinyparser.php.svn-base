<?
class Parser
{
	var $app;
	var $page;

	function Parser($app)
	{
		$this->app = &$app;

		$rootPage = $_SERVER['DOCUMENT_ROOT']."/".$this->app->Config->tplPath.$this->app->Config->page;

		// Lade Hauptseite
		if(file_exists($rootPage))
	  	$this->page = file_get_contents($rootPage);
		else
			$this->page = "";

		$this->app = $app;
	}

	function Parse($tag, $template)
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/".$this->app->Config->tplPath.$template;
		
		if(file_exists($path) && $tag!="")
		{
			$content = file_get_contents($path);
			$this->page = str_replace($tag, $content, $this->page);
		}	
	}
	function FinalParse($template)
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/".$this->app->Config->tplPath.$template;
		
		if(file_exists($path) && $tag!="")
		{
			$content = file_get_contents($path);
			$this->page = str_replace($tag, $content, $this->page);
		}	
	}



	function Set($tag, $text)
	{
		if($text!="" && $tag!="")
			$this->page = str_replace($tag, $text, $this->page);
	}


	function Show()
	{
		// Leere Tags entfernen
		preg_match_all("/\[[a-z].*\]/i", $this->page, $matches);

		for($i=0;$i<count($matches[0]);$i++)
			$this->page = str_replace($matches[0][$i], "", $this->page);

		// zeige seite an
		echo $this->page;			
	}


}
?>
