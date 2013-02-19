<?php

class Network 
{

  function Network(&$app)
  {
    $this->app=&$app; 
  
		$this->file = 'modules/network/network';


    $this->app->ActionHandlerInit($this);

    $this->app->ActionHandler("list","NetworkList");
    $this->app->ActionHandler("refresh","NetworkRefresh");
    $this->app->ActionHandler("set","NetworkSet");
  
    $this->app->DefaultActionHandler("list");
    $this->app->Tpl->ReadTemplatesFromPath("./pages/content/");

    $this->app->ActionHandlerListen(&$app);

  }


  function NetworkList()
  {
    $this->app->Tpl->Parse(PAGE,"network_list.tpl");
  }

	function NetworkRefresh()
	{
		$interfaces = $this->ParseFile($this->file);
		
		echo json_encode($interfaces);
		exit;
	}

	function NetworkSet()
	{
		$data = json_decode($_GET['data']);
		
		if(is_array($data) && count($data)>0) {
			$this->WriteFile($this->file, $data);
			//exec("/etc/init.d/networking restart");
		}
 
		exit;
	}

	function WriteFile($file, $data)
	{
		$out = '';
		for($i=0;$i<count($data);$i++) {
			
			$interface = trim($data[$i]->interface);
			$type = trim($data[$i]->type);
			$dhcp = trim($data[$i]->dhcp);
			$address = trim($data[$i]->address);
			$netmask = trim($data[$i]->netmask);
			$gateway = trim($data[$i]->gateway);
			$broadcast = trim($data[$i]->broadcast);
			$nameserver = trim($data[$i]->nameserver);



			$out .= "iface $interface $type $dhcp\n";
			if($address!='') $out .= "address $address\n";
			if($netmask!='') $out .= "netmask $netmask\n";
			if($gateway!='') $out .= "gateway $gateway\n";
			if($broadcast!='') $out .= "broadcast $broadcast\n";
			if($nameserver!='') $out .= "nameserver $nameserver\n";

			$out .= "\n";
		}

		file_put_contents($file, $out);
	}

	function ParseFile($file)
	{
		$data = file_get_contents($file);

		$lines = explode("\n", $data);

		$if_index = 0;
		$interface = array();

		for($i=0;$i<count($lines);$i++) {

			if($this->Search('iface', $lines[$i])) {
				$interface[$if_index] = $this->ParseInterface($lines[$i]);
				
				for($l=($i+1);$l<count($lines);$l++) {
					if($this->Search('iface', $lines[$l]))
						break;

					if($this->Search('address', $lines[$l]))
						$interface[$if_index]['address'] = $this->GetValue($lines[$l], 'address');

					if($this->Search('network', $lines[$l]))
            $interface[$if_index]['network'] = $this->GetValue($lines[$l], 'network');

					if($this->Search('netmask', $lines[$l]))
            $interface[$if_index]['netmask'] = $this->GetValue($lines[$l], 'netmask');

					if($this->Search('broadcast', $lines[$l]))
					  $interface[$if_index]['broadcast'] = $this->GetValue($lines[$l], 'broadcast');

					if($this->Search('gateway', $lines[$l]))
            $interface[$if_index]['gateway'] = $this->GetValue($lines[$l], 'gateway');

					if($this->Search('nameserver', $lines[$l]))
	          $interface[$if_index]['nameserver'] = $this->GetValue($lines[$l], 'nameserver');
				}

				$if_index++;
			}

		}

		return $interface;
	}

	function GetValue($line, $prefix)
	{
		$line = str_replace("\n", '', $line);

		$len = strlen($line);

		$start = strpos($line, $prefix);
		
		if($start!==false) {
			$prefix_end = $start+strlen($prefix);
			return trim(substr($line, $prefix_end, $len-$prefix_end));
		}
		return '';
	}

	function ParseInterface($line)
	{
		$cols = explode(' ', $line);

		return array('interface'=>$cols[1],
								 'type'=>$cols[2],
								 'dhcp'=>$cols[3]);
	}

	function Search($search, $str) {
		if(strpos($str, $search)===false)
			return false;
		return true;
	}

}
?>
