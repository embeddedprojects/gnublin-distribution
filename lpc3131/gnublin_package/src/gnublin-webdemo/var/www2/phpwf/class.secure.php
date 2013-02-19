<?
class Secure
{
	var $app;
	var $content;
	var $message;

	function Secure($app)
	{
		$this->app = &$app;

		$this->pattern = "/[a-z0-9.@\s-]/i";

		$this->app = $app;
	}

	function Filter($input)
	{
		$valid = "";

		preg_match_all($this->pattern, $input, $matches);

		for($i=0;$i<count($matches[0]);$i++)
			$valid .= $matches[0][$i];

		return $valid;
	}

	function GetPOST($element)
	{
		$data = $_POST[$element];

		return $this->Filter($data);
	}

	function GetGET($element)
	{
		$data = $_GET[$element];

		return $this->Filter($data);
	}

}
?>
