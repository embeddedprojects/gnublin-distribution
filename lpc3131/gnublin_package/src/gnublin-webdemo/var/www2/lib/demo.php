<?
class Demo
{
	var $app;

	function Demo($app)
	{
		$this->app = &$app;


		$this->app = $app;
	}

	function Create($kennung)
	{
		if($kennung=="")
		{
			echo "Error: Kennung fehlt";
			exit;
		}
		$path = $_SERVER["DOCUMENT_ROOT"]."/".$this->app->Config->kisPath.$kennung;


		// Benutzerverzeichnis anlegen
		system("mkdir $path");

		// Benoetigte Ordner anlegen
		system("mkdir $path/conf");
		system("mkdir $path/backup");
		system("mkdir $path/userdata");
		system("mkdir $path/userdata/dms");
		system("mkdir $path/www");
		system("mkdir $path/phpwf");

		// Symbolische Links erzeugen
		//system("ln -sn ../../wawision/www/ $path/www");
		//system("ln -sn ../../wawision/phpwf/ $path/phpwf");
		//system("ln -s ../../../wawision/main.conf.php $path/conf/main.conf.php");
		system("cp wawision/main.conf.php $path/conf/main.conf.php");
		system("chmod 755 $path/conf/main.conf.php");
		
		// Ordner kopieren
		system("cp -R wawision/www/* $path/www");
		system("cp -R wawision/phpwf/* $path/phpwf");


		// user.inc.php.tpl auslesen, veraendern und unbennenen
		$content = file_get_contents("wawision/user.inc.php.tpl");
		$content = str_replace("WFdbhost='localhost'", "WFdbhost='{$this->app->Config->sqlhost}'", $content);
		$content = str_replace("WFdbuser='wawision'", "WFdbuser='{$this->app->Config->sqluser}'", $content);
		$content = str_replace("WFdbpass='DBPASS'", "WFdbpass='{$this->app->Config->sqlpassword}'", $content);
		$content = str_replace("WFdbname='wawision'", "WFdbname='$kennung'", $content);
		$content = str_replace("WFdemo='false'", "WFdemo='true'", $content);
		$file = fopen("$path/conf/user.inc.php","w");
		fwrite($file, $content);
		fclose($file);

		// Datenbank erzeugen
		system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -e 'CREATE DATABASE $kennung;'");


		// Struktur erzeugen
		system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -D$kennung < wawision/database/main.sql");

		// Zu Datenbank wechseln
		$this->app->DB->SelectDB($kennung);

		// Initiale Daten erzeugen
		$sql = 'INSERT INTO `adresse` (`id`, `typ`, `marketingsperre`, `trackingsperre`, `rechnungsadresse`, `sprache`, `name`, `abteilung`, `unterabteilung`, `ansprechpartner`, `land`, `strasse`, `ort`, 
		        `plz`, `telefon`, `telefax`, `mobil`, `email`, `ustid`, `ust_befreit`, `passwort_gesendet`, `sonstiges`, `adresszusatz`, `kundenfreigabe`, `steuer`, `logdatei`, `kundennummer`, 
						`lieferantennummer`, `mitarbeiternummer`, `konto`, `blz`, `bank`, `inhaber`, `swift`, `iban`, `waehrung`, `paypal`, `paypalinhaber`, `paypalwaehrung`, `projekt`, `partner`, `geloescht`, 
						`firma`) VALUES (NULL, \'\', \'\', \'\', \'\', \'\', \'Administrator\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', NOW(), 
						\'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'1\', \'\', \'\', \'1\');';
		$this->app->DB->Insert($sql);

		$sql = 'INSERT INTO `firma` (`id`, `name`, `standardprojekt`) VALUES (NULL, \'Musterfirma\', \'1\');';
		$this->app->DB->Insert($sql);

		$sql = 'INSERT INTO `user` (`id`, `username`, `password`, `repassword`, `description`, `settings`, `parentuser`, `activ`, `type`, `adresse`, `standarddrucker`, `firma`, `logdatei`) 
		        VALUES (NULL, \'admin\', ENCRYPT(\'admin\'), \'\', NULL, \'\', NULL, \'1\', \'admin\', \'1\', \'\', \'1\', NOW());';
		$this->app->DB->Insert($sql);

		$sql = 'INSERT INTO `projekt` (`id`, `name`, `abkuerzung`, `verantwortlicher`, `beschreibung`, `sonstiges`, `aktiv`, `farbe`, `autoversand`, `checkok`, `checkname`, `zahlungserinnerung`, 
		       `zahlungsmailbedinungen`, `folgebestaetigung`, `kundenfreigabe_loeschen`, `autobestellung`, `firma`, `logdatei`) VALUES (NULL, \'Hauptprojekt\', \'HAUPTPROJEKT\', \'\', \'\', \'\', \'\', \'\'                 , \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'1\', \'\');';
		$this->app->DB->Insert($sql);
	 
	 	$sql = "INSERT INTO `firmendaten` (`id`, `firma`, `absender`, `sichtbar`, `barcode`, `schriftgroesse`, `betreffszeile`, `dokumententext`, `tabellenbeschriftung`, `tabelleninhalt`, `zeilenuntertext`, 
					 `freitext`, `infobox`, `spaltenbreite`, `footer_0_0`, `footer_0_1`, `footer_0_2`, `footer_0_3`, `footer_0_4`, `footer_0_5`, `footer_1_0`, `footer_1_1`, `footer_1_2`, `footer_1_3`, `footer_1_4`,   
					 `footer_1_5`, `footer_2_0`, `footer_2_1`, `footer_2_2`, `footer_2_3`, `footer_2_4`, `footer_2_5`, `footer_3_0`, `footer_3_1`, `footer_3_2`, `footer_3_3`, `footer_3_4`, `footer_3_5`, 
					 `footersichtbar`, `hintergrund`, `logo`, `logo_type`, `briefpapier`, `briefpapier_type`, `benutzername`, `passwort`, `host`, `port`, `mailssl`, `signatur`, `email`, `absendername`,
					 `bcc1`, `bcc2`, `firmenfarbe`, `name`, `strasse`, `plz`, `ort`, `steuernummer`, `datum`, `projekt`) VALUES
					 (1, 1, 'Musterfirma GmbH | Musterweg 5 | 12345 Musterstadt', 1, 0, 7, 9, 9, 9, 9, 7, 9, 8, 0, 'Sitz der Gesellschaft / Lieferanschrift', 'Musterfirma GmbH', 'Musterweg 5', 
					 'D-12345 Musterstadt', 'Telefon +49 123 12 34 56 7', 'Telefax +49 123 12 34 56 78', 'Bankverbindung', 'Musterbank', 'Konto 123456789', 'BLZ 72012345', '', '', 'IBAN DE1234567891234567891', 
					 'BIC/SWIFT DETSGDBWEMN', 'Ust-IDNr. DE123456789', 'E-Mail: info@musterfirma-gmbh.de', 'Internet: http://www.musterfirma.de', '', 'Gesch&auml;ftsf&uuml;hrer', 'Max Musterman', 
					 'Handelsregister: HRB 12345', 'Amtsgericht: Musterstadt', '', '', 0, 'kein', '', '', '', '', 'musterman', 'passwort', 'smtp.server.de', '25', 1, 
           'LS0NCk11c3RlcmZpcm1hIEdtYkgNCk11c3RlcndlZyA1DQpELTEyMzQ1IE11c3RlcnN0YWR0DQoNClRlbCArNDkgMTIzIDEyIDM0IDU2IDcNCkZheCArNDkgMTIzIDEyIDM0IDU2IDc4DQoNCk5hbWUgZGVyIEdlc2VsbHNjaGFmdDogTXVzdGVyZmlybWEgR21iSA0KU2l0eiBkZXIgR2VzZWxsc2NoYWZ0OiBNdXN0ZXJzdGFkdA0KDQpIYW5kZWxzcmVnaXN0ZXI6IE11c3RlcnN0YWR0LCBIUkIgMTIzNDUNCkdlc2Now6RmdHNmw7xocnVuZzogTWF4IE11c3Rlcm1hbg0KVVN0LUlkTnIuOiBERTEyMzQ1Njc4OQ0KDQpBR0I6IGh0dHA6Ly93d3cubXVzdGVyZmlybWEuZGUvDQo=', 'info@server.de', 'Meine Firma', '', '', '', 'Musterfirma GmbH', 'Musterweg 5', '12345', 'Musterstadt', '111/11111/11111', '0000-00-00 00:00:00', 1);";
		$this->app->DB->Insert($sql);


		// Zur systemeigenen Datenbank zurueckwechseln
		$this->app->DB->SelectDB($this->app->Config->sqldatabase);
	}

	function CreateShop($kennung)
	{
    if($kennung=="")
    {
      echo "Error: Kennung fehlt";
      exit;
    }

		$path = $_SERVER["DOCUMENT_ROOT"]."/".$this->app->Config->kisPath.$kennung;

		// Ordner anlegen
		system("mkdir $path/shop");
    system("mkdir $path/shop/conf");
    system("mkdir $path/shop/phpwf");
    system("mkdir $path/shop/webroot");

		// Ordner kopieren
  	system("cp -R shop/phpwf/* $path/shop/phpwf");
    system("cp -R shop/webroot/* $path/shop/webroot");	

		// Main.conf.php kopieren
		system("cp shop/main.conf.php $path/shop/conf/main.conf.php");
    system("chmod 755 $path/shop/conf/main.conf.php");

		// user.inc.php.tpl auslesen, veraendern und unbennenen
    $content = file_get_contents("shop/user.inc.php.tpl");
    $content = str_replace("WFdbhost='localhost'", "WFdbhost='{$this->app->Config->sqlhost}'", $content);
    $content = str_replace("WFdbuser='wawision'", "WFdbuser='{$this->app->Config->sqluser}'", $content);
    $content = str_replace("WFdbpass='DBPASS'", "WFdbpass='{$this->app->Config->sqlpassword}'", $content);
    $content = str_replace("WFdbname='wawision'", "WFdbname='{$kennung}_shop'", $content);
    //$content = str_replace("WFdemo='false'", "WFdemo='true'", $content);
    $file = fopen("$path/shop/conf/user.inc.php","w");
    fwrite($file, $content);
    fclose($file);

		// Datenbank erzeugen
    system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -e 'CREATE DATABASE ".$kennung."_shop;'");

    // Struktur erzeugen
    system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -D".$kennung."_shop < shop/database/shop.sql");
		
		// Zu Datenbank wechseln
    $this->app->DB->SelectDB($kennung);

		// Stelle Verbindung zwischen wawision und shop her
		$url = "http://{$_SERVER[SERVER_NAME]}/{$this->app->Config->kisPath}$kennung/shop/webroot/";
		$this->app->DB->Insert("INSERT INTO shopexport (bezeichnung, typ, url, passwort, token, challenge, projekt, cms, firma)
														VALUES ('Mein Test-Shop', 'wawision', '$url', 'abcdefghijuklmno0123456789012345', '12345', '12345', '1', '0', '1')");

		// Zur systemeigenen Datenbank zurueckwechseln
    $this->app->DB->SelectDB($this->app->Config->sqldatabase);
	}

	function CreateManagment($kennung)
	{
		if($kennung=="")
    { 
      echo "Error: Kennung fehlt";
      exit;
    }

		$path = $_SERVER["DOCUMENT_ROOT"]."/".$this->app->Config->kisPath.$kennung;

		system("mkdir $path/managment");
  	system("cp -R managment/* $path/managment");

		// Passe index.php an 
		$content = file_get_contents("$path/managment/index.php");
		$content = str_replace("[KENNUNG]", $kennung, $content);
		$file = fopen("$path/managment/index.php","w");
    fwrite($file, $content);
    fclose($file);

		// Erzeuge .htpasswd
		//system("htpasswd -cbm $path/managment/.htpasswd ");
	}


	function Remove($kennung)
	{
		if($kennung=="")
		{
			echo "Error: Kennung fehlt";
			exit;
		}

		$path = $_SERVER["DOCUMENT_ROOT"]."/".$this->app->Config->kisPath.$kennung;

		// Loesche Dateien
		if(is_dir($path))
			system("rm -r $path");

		// Loesche Datenbanken
		system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -e 'DROP DATABASE $kennung;'");
		system("mysql -u{$this->app->Config->sqluser} -p{$this->app->Config->sqlpassword} -e 'DROP DATABASE {$kennung}_shop;'");
	}

}
?>
