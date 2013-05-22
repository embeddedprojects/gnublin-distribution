<?php

class YUI
{

  function YUI(&$app)
  {
    $this->app = &$app;
  }


  function AARLGEditable()
  {
    $module = $this->app->Secure->GetGET("module");

    $table = $this->AARLGPositionenModule2Tabelle();

    $id = $this->app->Secure->GetPOST("id"); //ACHTUNG auftrag_positions tabelle id
   
    $tmp = split('split',$id);

    $id = $tmp[0];
   
    $column = $tmp[1];

    $value = $this->app->Secure->GetPOST("value");
    $cmd = $this->app->Secure->GetGET("cmd");

    switch($column)
    {
      case 3: // Datum
        $value = $this->app->String->Convert($value,"%1.%2.%3","%3-%2-%1");
        $this->app->DB->Update("UPDATE $table SET lieferdatum='$value' WHERE id='$id' LIMIT 1");
        $result = $this->app->DB->Select("SELECT lieferdatum FROM $table WHERE id='$id' LIMIT 1");
        $result = $this->app->String->Convert($result,"%3-%2-%1","%1.%2.%3");
      break;

      case 4: // Menge
        $this->app->DB->Update("UPDATE $table SET menge='$value' WHERE id='$id' LIMIT 1");
        $result = $this->app->DB->Select("SELECT menge FROM $table WHERE id='$id' LIMIT 1");
      break;
      case 5: //preis
        $value = str_replace(",",".",$value);
        $this->app->DB->Update("UPDATE $table SET preis='$value' WHERE id='$id' LIMIT 1");
        $result = $this->app->DB->Select("SELECT preis FROM $table WHERE id='$id' LIMIT 1");
      break;
      default:
      ;
    }



    if($cmd=="load")
      echo "Load";
    else
      echo $result;
    exit;

  }



  function AARLGPositionenModule2Tabelle()
  {
    $module = $this->app->Secure->GetGET("module");
    if($module=="auftrag") $table = "auftrag_position";
    else if($module=="angebot") $table = "angebot_position";
    else if($module=="lieferschein") $table = "lieferschein_position";
    else if($module=="rechnung") $table = "rechnung_position";
    else if($module=="gutschrift") $table = "gutschrift_position";
    else if($module=="bestellung") $table = "bestellung_position";
    else exit;
    return $table;


  }



  function AARLGPositionen($iframe=true)
  {
    $module = $this->app->Secure->GetGET("module");
    $id = $this->app->Secure->GetGET("id");

    if($iframe)
    {
      $this->app->Tpl->Set(POS,"<iframe style=\"\" src=\"index.php?module=$module&action=positionen&id=$id\" frameborder=\"no\" width=\"750\" height=\"850\"></iframe>");
    }
    else {

    $table = $this->AARLGPositionenModule2Tabelle();
  
    /* neu anlegen formular */
    $artikelart = $this->app->Secure->GetPOST("artikelart");
    $bezeichnung = $this->app->Secure->GetPOST("bezeichnung");
    $vpe = $this->app->Secure->GetPOST("vpe");
    $umsatzsteuerklasse = $this->app->Secure->GetPOST("umsatzsteuerklasse");
    $waehrung = $this->app->Secure->GetPOST("waehrung");
    $projekt= $this->app->Secure->GetPOST("projekt");
    $preis = $this->app->Secure->GetPOST("preis");
    $preis = str_replace(',','.',$preis);
    $menge = $this->app->Secure->GetPOST("menge");
    $lieferdatum = $this->app->Secure->GetPOST("lieferdatum");
    $lieferdatum  = $this->app->String->Convert($lieferdatum,"%1.%2.%3","%3-%2-%1");


    if($lieferdatum=="") $lieferdatum="00.00.0000";

    $ajaxbuchen = $this->app->Secure->GetPOST("ajaxbuchen");
    if($ajaxbuchen!="")
    { 
      $artikel = $this->app->Secure->GetPOST("artikel");
      $nummer = $this->app->Secure->GetPOST("nummer");
      $projekt = $this->app->Secure->GetPOST("projekt");
      $projekt = $this->app->DB->Select("SELECT id FROM projekt WHERE abkuerzung='$projekt' LIMIT 1");
      $sort = $this->app->DB->Select("SELECT MAX(sort) FROM $table WHERE $module='$id' LIMIT 1");
      $sort = $sort + 1;
      $artikel_id = $this->app->DB->Select("SELECT id FROM artikel WHERE nummer='$nummer' LIMIT 1");
      $bezeichnung = $artikel;
      $neue_nummer = $nummer;
      $waehrung = 'EUR';
      $umsatzsteuer = $this->app->DB->Select("SELECT umsatzsteuer FROM artikel WHERE nummer='$nummer' LIMIT 1");
      $vpe = 'einzeln';

      if($module=="lieferschein")
      {
      $this->app->DB->Insert("INSERT INTO $table (id,$module,artikel,bezeichnung,nummer,menge,sort,lieferdatum, status,projekt,vpe)
          VALUES ('','$id','$artikel_id','$bezeichnung','$neue_nummer','$menge','$sort','$lieferdatum','angelegt','$projekt','$vpe')");

      }
      else if($module=="bestellung")
      {

	$bestellnummer = $this->app->Secure->GetPOST("bestellnummer");
	$bezeichnunglieferant = $this->app->Secure->GetPOST("bezeichnunglieferant");
	// hier muesste man beeichnung bei lieferant auch noch speichern .... oder beides halt


	$this->app->DB->Insert("INSERT INTO $table (id,$module,artikel,bezeichnunglieferant,bestellnummer,menge,sort,lieferdatum, status,projekt,vpe,preis,waehrung,umsatzsteuer)
          VALUES ('','$id','$artikel_id','$bezeichnunglieferant','$bestellnummer','$menge','$sort','$lieferdatum','angelegt','$projekt','$vpe','$preis','$waehrung','$umsatzsteuer')");
      }
      else {
        $this->app->DB->Insert("INSERT INTO $table (id,$module,artikel,bezeichnung,nummer,menge,preis, waehrung, sort,lieferdatum, umsatzsteuer, status,projekt,vpe)
          VALUES ('','$id','$artikel_id','$bezeichnung','$neue_nummer','$menge','$preis','$waehrung','$sort','$lieferdatum','$umsatzsteuer','angelegt','$projekt','$vpe')");
      }
    }


      /* ende neu anlegen formular */


      $this->app->Tpl->Set(SUBSUBHEADING,"Positionen");


      $menu = array("up"=>"up{$module}position",
                          "down"=>"down{$module}position",
                          //"add"=>"addstueckliste",
                          "edit"=>"positioneneditpopup",
                          "del"=>"del{$module}position");

      if($module=="auftrag")
      {
 $sql = "SELECT if(b.beschreibung!='',
		if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(CONCAT(b.bezeichnung,' *'),1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),CONCAT(b.bezeichnung,' *')),
						if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(b.bezeichnung,1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),b.bezeichnung))
						as Artikel,



		p.abkuerzung as projekt, b.nummer as nummer, DATE_FORMAT(lieferdatum,'%d.%m.%Y') as lieferdatum, b.menge as menge, b.preis as preis, b.id as id
                FROM $table b
                LEFT JOIN artikel a ON a.id=b.artikel LEFT JOIN projekt p ON b.projekt=p.id
                WHERE b.$module='$id' ";
                //WHERE b.$module='$id' AND b.explodiert_parent='0'";
      }

      else if($module=="lieferschein")
      {
  $sql = "SELECT if(b.beschreibung!='',
		if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(CONCAT(b.bezeichnung,' *'),1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),CONCAT(b.bezeichnung,' *')),
						if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(b.bezeichnung,1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),b.bezeichnung))
						as Artikel,


		p.abkuerzung as projekt, b.nummer as nummer, DATE_FORMAT(lieferdatum,'%d.%m.%Y') as lieferdatum, b.menge as menge, if(b.geliefert, b.geliefert,'-') as geliefert, b.id as id
                FROM $table b
                LEFT JOIN artikel a ON a.id=b.artikel LEFT JOIN projekt p ON b.projekt=p.id
                WHERE b.$module='$id'";
      } 

      else if($module=="bestellung")
      {
  $sql = "SELECT if(b.beschreibung!='',
		if(CHAR_LENGTH(b.bezeichnunglieferant)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(CONCAT(b.bezeichnunglieferant,' *'),1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),CONCAT(b.bezeichnunglieferant,' *')),
						if(CHAR_LENGTH(b.bezeichnunglieferant)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(b.bezeichnunglieferant,1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),b.bezeichnunglieferant))
						as Artikel,
		p.abkuerzung as projekt,  a.nummer as nummer, DATE_FORMAT(lieferdatum,'%d.%m.%Y') as lieferdatum, b.menge as menge, b.preis as preis, b.id as id
                FROM $table b
                LEFT JOIN artikel a ON a.id=b.artikel LEFT JOIN projekt p ON b.projekt=p.id
                WHERE b.$module='$id'";

      } 

      else {
      //$sql = "SELECT if(b.beschreibung!='',if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(CONCAT(b.bezeichnung,' *'),1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),CONCAT(b.bezeichnung,' *'),SUBSTR(b.bezeichnung,1,".$this->app->erp->MaxArtikelbezeichnung().")) as Artikel,
      $sql = "SELECT if(b.beschreibung!='',
		if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(CONCAT(b.bezeichnung,' *'),1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),CONCAT(b.bezeichnung,' *')),
						if(CHAR_LENGTH(b.bezeichnung)>".$this->app->erp->MaxArtikelbezeichnung().",CONCAT(SUBSTR(b.bezeichnung,1,".$this->app->erp->MaxArtikelbezeichnung()."),'...'),b.bezeichnung))
						as Artikel,

		p.abkuerzung as projekt, a.nummer as nummer, b.nummer as nummer, DATE_FORMAT(lieferdatum,'%d.%m.%Y') as lieferdatum, b.menge as menge, b.preis as preis, b.id as id
                FROM $table b
                LEFT JOIN artikel a ON a.id=b.artikel LEFT JOIN projekt p ON b.projekt=p.id
                WHERE b.$module='$id'";
      }

      //$this->app->Tpl->Add(EXTEND,"<input type=\"submit\" value=\"Gleiche Positionen zusammenf&uuml;gen\">");

      $this->app->YUI->SortListAdd(TAB1,&$this,$menu,$sql);

      $this->app->Tpl->Add(TAB1,"<br><center><!--<input type=\"button\" value=\"Gleiche Positionen zusammenf&uuml;gen\">&nbsp;-->
          &nbsp;<input type=\"button\" value=\"Artikel: Suche oder Neu anlegen\" onclick=\"window.location.href='index.php?module=artikel&action=profisuche&cmd={$module}&id=$id';\"></center>");

      $this->app->BuildNavigation=false;

      $this->app->Tpl->Add(PAGE,"<br><fieldset><legend>Positionen</legend>");
      $this->app->Tpl->Parse(PAGE,"auftrag_positionuebersicht.tpl");
      $this->app->Tpl->Add(PAGE,"</fieldset>");



    }


  }


  function ParserVarIf($parsvar,$choose)
  {
    if($choose==0)
    {
	$this->app->Tpl->Set($parsvar."IF","<!--");
	$this->app->Tpl->Set($parsvar."ELSE","-->");
	$this->app->Tpl->Set($parsvar."ENDIF","");
    } else {
	$this->app->Tpl->Set($parsvar."IF","");
	$this->app->Tpl->Set($parsvar."ELSE","<!--");
	$this->app->Tpl->Set($parsvar."ENDIF","-->");
    }

  }
  

  function DatePicker($name)
  {
	  $this->app->Tpl->Add(JQUERY,'$( "#'.$name.'" ).datepicker({ dateFormat: \'dd.mm.yy\' });');  
  }


  function Message($class,$msg)
  {
    $this->app->Tpl->Add(MESSAGE,"<div class=\"$class\">$msg</div>");
  }


  function IconsSQL()
  {

    $go_lager = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/lagergo.png\" title=\"Artikel ist im Lager\" border=\"0\">";
    $stop_lager = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/lagerstop.png\" title=\"Artikel fehlt im Lager\" border=\"0\">";

    $go_porto = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/portogo.png\" title=\"Porto Check OK\" border=\"0\">";
    $stop_porto = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/portostop.png\" title=\"Porto fehlt!\" border=\"0\">";

    $go_ust = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/ustgo.png\" title=\"UST Check OK\" border=\"0\">";
    $stop_ust = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/uststop.png\" title=\"UST-Pr&uuml;fung fehlgeschlagen!\" border=\"0\">";


    $go_vorkasse = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/vorkassego.png\" title=\"Zahlungscheck OK\" border=\"0\">";
    $stop_vorkasse = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/vorkassestop.png\" title=\"Zahlungseingang bei Vorkasse fehlt!\" border=\"0\">";

    $go_nachnahme = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/nachnahmego.png\" title=\"Nachnahme Check OK\" border=\"0\">";
    $stop_nachnahme = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/nachnahmestop.png\" title=\"Nachnahmegeb&uuml;hr fehlt!\" border=\"0\">";


    $go_autoversand = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/autoversandgo.png\" title=\"Autoversand erlaubt\" border=\"0\">";
    $stop_autoversand = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/autoversandstop.png\" title=\"Kein Autoversand erlaubt!\" border=\"0\">";


    $go_check = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/checkgo.png\" title=\"Kundencheck OK\" border=\"0\">";
    $stop_check = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/checkstop.png\" title=\"Kundencheck fehlgeschlagen\" border=\"0\">";


    $reserviert = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/reserviert.png\" border=\"0\">";
    $check = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/mail-mark-important.png\" border=\"0\">";
    
    $abgeschlossen = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/grey.png\" title=\"Auftrag abgeschlossen\" border=\"0\">";
    $angelegt = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/blue.png\" title=\"Auftrag noch nicht freigegeben!\" border=\"0\">";
    $storniert = "<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/storno.png\" title=\"Auftrag storniert!\" border=\"0\">";

    for($i=0;$i<7;$i++)
      $tmp .= $abgeschlossen;

    for($i=0;$i<7;$i++)
      $tmpblue .= $angelegt;


    for($i=0;$i<7;$i++)
      $tmpstorno .= $storniert;


    return "if(a.status='angelegt','<table cellpadding=0 cellspacing=0><tr><td nowrap>$tmpblue</td></tr></table>',
	      if(a.status='abgeschlossen' or a.status='storniert',
	  if(a.status='abgeschlossen','<table cellpadding=0 cellspacing=0><tr><td nowrap>$tmp</td></tr></table>','<table cellpadding=0 cellspacing=0><tr><td nowrap>$tmpstorno</td></tr></table>'),

	  CONCAT('<table cellpadding=0 cellspacing=0><tr><td nowrap>',
	if(a.lager_ok,'$go_lager','$stop_lager'),if(a.porto_ok,'$go_porto','$stop_porto'),if(a.ust_ok,'$go_ust',CONCAT('<a href=\"/index.php?module=adresse&action=ustprf&id=',a.adresse,'\">','$stop_ust','</a>')),
	if(a.vorkasse_ok,'$go_vorkasse','$stop_vorkasse'),if(a.nachnahme_ok,'$go_nachnahme','$stop_nachnahme'),if(a.autoversand,'$go_autoversand','$stop_autoversand'),
	if(a.check_ok,'$go_check','$stop_check'),'</td></tr></table>'
	)))";
  }  


  function TablePositionSearch($parsetarget,$name,$callback="show")
  {
	  $id = $this->app->Secure->GetGET("id");

	  switch($name)
	  {
	    case "auftragpositionen":
/*
	      // headings
	      $heading =  array('Nummer','Artikel','Projekt','Menge','Einzelpreis','Men&uuml;');
	      $width   =  array('10%','45%','15%','10%','10%','10%');
	      $findcols = array('nummer','name_de','projekt','menge','preis','id');
	      $searchsql = array('a.bezeichnung','a.nummer','p.abkuerzung');

	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.nummer as nummer, a.bezeichnung as name_de, p.abkuerzung as projekt, a.menge as menge, a.preis as preis, a.id as menu
		  FROM  auftrag_position a LEFT JOIN projekt p ON p.id=a.projekt ";

	      // fester filter
	      $where = " a.auftrag='$id'";

	      $count = "SELECT COUNT(id) FROM auftrag_position WHERE auftrag='$id'";
*/
	    break;
   
	    default:
	      break;
	  }



	  if($callback=="show")
	  {

	    $this->app->Tpl->Add(ADDITIONALCSS,"

.ex_highlight #$name tbody tr.even:hover, #example tbody tr.even td.highlighted {
  background-color: #ECFFB3; 
}

.ex_highlight_row #$name tr.even:hover {
  background-color: #ECFFB3;
}

.ex_highlight_row #$name tr.even:hover td.sorting_1 {
  background-color: #DDFF75;
}

.ex_highlight_row #$name tr.odd:hover {
  background-color: #E6FF99;
}

.ex_highlight_row #$name tr.odd:hover td.sorting_1 {
  background-color: #D6FF5C;
}
");

		      //"sPaginationType": "full_numbers",
		  //"aLengthMenu": [[10, 25, 50, 200, 10000], [10, 25, 50, 200, "All"]],


$this->app->Tpl->Add(JAVASCRIPT," var oTable".$name."; var oMoreData1".$name."=0; var oMoreData2".$name."=0; var oMoreData3".$name."=0; var oMoreData4".$name."=0; var oMoreData5".$name."=0;  var aData;
  ");

	    $this->app->Tpl->Add(DATATABLES,
'
	     oTable'.$name.' = $(\'#'.$name.'\').dataTable( {
		  "bProcessing": true,
		  "iDisplayLength": 10,
		      "bStateSave": true,
		  "bServerSide": true,
    "fnServerData": function ( sSource, aoData, fnCallback ) {
      /* Add some extra data to the sender */
      aoData.push( { "name": "more_data1", "value": oMoreData1'.$name.' } );
      aoData.push( { "name": "more_data2", "value": oMoreData2'.$name.' } );
      aoData.push( { "name": "more_data3", "value": oMoreData3'.$name.' } );
      aoData.push( { "name": "more_data4", "value": oMoreData4'.$name.' } );
      aoData.push( { "name": "more_data5", "value": oMoreData5'.$name.' } );
      $.getJSON( sSource, aoData, function (json) { 
	/* Do whatever additional processing you want on the callback, then tell DataTables */
	fnCallback(json)
      } );
    },

		  "sAjaxSource": "./index.php?module=ajax&action=tableposition&cmd='.$name.'&id='.$id.'"
		} );

	      ');
if($moreinfo)
{
$this->app->Tpl->Add(DATATABLES,
'
$(\'#'.$name.' tbody td img\').live( \'click\', function () {
    var nTr = this.parentNode.parentNode;
    aData =  oTable'.$name.'.fnGetData( nTr );

    if ( this.src.match(\'details_close\') )
    {
      /* This row is already open - close it */
      this.src = "./themes/'.$this->app->Conf->WFconf[defaulttheme].'/images/details_open.png";
      oTable'.$name.'.fnClose( nTr );
    }
    else
    {
      /* Open this row */
      this.src = "./themes/'.$this->app->Conf->WFconf[defaulttheme].'/images/details_close.png";
      oTable'.$name.'.fnOpen( nTr, '.$name.'fnFormatDetails(nTr), \'details\' );
    }
  });
');
/*  $.get("index.php?module=auftrag&action=minidetail&id=2", function(text){
    spin=0; 
    miniauftrag = text;
  });
*/

$module = $this->app->Secure->GetGET("module");

$this->app->Tpl->Add(JAVASCRIPT,'function '.$name.'fnFormatDetails ( nTr ) {
  //var aData =  oTable'.$name.'.fnGetData( nTr );
  var str = aData['.$menucol.'];
  var match = str.match(/[1-9]{1}[0-9]*/);

  var auftrag = parseInt(match[0], 10);

  var miniauftrag;
  var strUrl = "index.php?module='.$module.'&action=minidetail&id="+auftrag; //whatever URL you need to call
  var strReturn = "";

  jQuery.ajax({
    url:strUrl, success:function(html){strReturn = html;}, async:false
  });

  miniauftrag = strReturn;

  var sOut = \'<table cellpadding="0" cellspacing="0" border="0" align="center" style="padding-left: 30px;">\';
  sOut += \'<tr><td>\'+miniauftrag+\'</td></tr>\';
  sOut += \'</table>\';
  return sOut;
}
');
  


}



      $colspan = count($heading);

      $this->app->Tpl->Add($parsetarget,'
	<br><br>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="'.$name.'">
	  <thead>
	    <tr><th colspan="'.$colspan .'"><br></th></tr>
	    <tr>');

	for($i=0;$i<count($heading);$i++)
	{
	    $this->app->Tpl->Add($parsetarget,'<th width="'.$width[$i].'">'.$heading[$i].'</th>');
	}

      $this->app->Tpl->Add($parsetarget,'</tr>
	  </thead>
	  <tbody>
	    <tr>
	      <td colspan="'.$colspan .'" class="dataTables_empty">Lade Daten</td>
	    </tr>
	  </tbody>

	  <tfoot>
	    <tr>
	');


	for($i=0;$i<count($heading);$i++)
	{
	    $this->app->Tpl->Add($parsetarget,'<th>'.$heading[$i].'</th>');
	}


	$this->app->Tpl->Add($parsetarget,'
	    </tr>
	  </tfoot>
	</table>
	<br>
	<br>
	<br>
	');
	    } else if ($callback=="sql")
	      return $sql;
	    else if ($callback=="searchsql")
	      return $searchsql; 
	    else if ($callback=="heading")
	      return $heading; 
	   else if ($callback=="menu")
	      return $menu; 
	  else if ($callback=="findcols")
	      return $findcols; 
	  else if ($callback=="where")
	      return $where; 
	  else if ($callback=="count")
	      return $count; 




  }



  function TableSearch($parsetarget,$name,$callback="show")
  {
	  $id = $this->app->Secure->GetGET("id");

	  switch($name)
	  {
	    case "kundeartikelpreise":
	      // alle artikel die ein Kunde kaufen kann mit preisen netto brutto

	      $cmd = $this->app->Secure->GetGET("smodule");
	      $adresse = $this->app->DB->Select("SELECT adresse FROM {$cmd} WHERE id='$id' LIMIT 1");


	      // headings
	      $heading =  array('Nummer','Artikel','Ab','Preis','Lager','Projekt','Men&uuml;');
	      $width   =  array('10%','45%','10%','10%','15%','10%');
	      $findcols = array('nummer','name_de','projekt','id');
	      $searchsql = array('a.name_de','a.nummer','p.abkuerzung');


	      $menu =
		  "<a href=\"#\" onclick=InsertDialog(\"index.php?module=artikel&action=profisuche&id=%value%&cmd=$cmd&sid=$id&insert=true\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/add.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.nummer as nummer, a.name_de as name_de, v.ab_menge as abmenge,v.preis as preis,a.cache_lagerplatzinhaltmenge as lager, p.abkuerzung as projekt, v.id as menu
		  FROM  verkaufspreise v, artikel a LEFT JOIN projekt p ON p.id=a.projekt  ";

	      // fester filter
	      $where = "a.geloescht=0 AND v.artikel=a.id AND (v.adresse='' OR v.adresse='$adresse' OR v.adresse='0') ";

	      $count = "SELECT COUNT(v.id) FROM verkaufspreise v, artikel a WHERE a.geloescht=0 AND v.artikel=a.id AND (v.adresse='' OR v.adresse='$adresse')";

	    break;

   	    case "lieferantartikelpreise":
	      // alle artikel die ein Kunde kaufen kann mit preisen netto brutto

	      $cmd = $this->app->Secure->GetGET("smodule");
	      $adresse = $this->app->DB->Select("SELECT adresse FROM {$cmd} WHERE id='$id' LIMIT 1");


	      // headings
	      $heading =  array('Nummer','Artikel','Ab','Preis','Lager','Projekt','Men&uuml;');
	      $width   =  array('10%','45%','10%','10%','15%','10%');
	      $findcols = array('nummer','name_de','projekt','id');
	      $searchsql = array('a.name_de','a.nummer','p.abkuerzung');

	      $menu =
		  "<a href=\"#\" onclick=InsertDialog(\"index.php?module=artikel&action=profisuche&id=%value%&cmd=$cmd&sid=$id&insert=true\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/add.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.nummer as nummer, a.name_de as name_de, v.ab_menge as abmenge,v.preis as preis,a.cache_lagerplatzinhaltmenge as lager, p.abkuerzung as projekt, v.id as menu
		  FROM  einkaufspreise v, artikel a LEFT JOIN projekt p ON p.id=a.projekt  ";

	      // fester filter
	      $where = "a.geloescht=0 AND v.artikel=a.id AND (v.adresse='' OR v.adresse='$adresse' OR v.adresse='0') AND (v.gueltig_bis='0000-00-00' OR v.gueltig_bis >=NOW()) ";

	      $count = "SELECT COUNT(v.id) FROM einkaufspreise v, artikel a WHERE a.geloescht=0 AND v.artikel=a.id AND (v.adresse='' OR v.adresse='$adresse')";

	    break;
   

	    case "lagertabelle":
	      // headings
	      $heading =  array('Bezeichnung','Beschreibung','Manuell','Men&uuml;');
	      $width   =  array('30%','20%','20%','20%');
	      $findcols = array('bezeichnung','Beschreibung','manuell','id');
	      $searchsql = array('bezeichnung','Beschreibung','manuell');


	      $menu =  "<a href=\"index.php?module=lager&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=lager&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=lager&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS id, bezeichnung, beschreibung, manuell, id as menu FROM lager ";

	      // fester filter
	      $where = " geloescht=0 AND id!=0";

	      $count = "SELECT COUNT(id) FROM lager WHERE geloescht=0";

	    break;
   
			case "adressestundensatz":
				$heading = array("Projekt-ID", "Projekt", "Typ", "Stundensatz", "Men&uuml;");
				$width = array("10%", "50%", "10%", "15%", "15%");
				$findcols = array("p.id", "p.name", "typ", "satz", "ssid");
				$searchsql = array("p.name");
				$sql = "SELECT SQL_CALC_FOUND_ROWS  p.id, p.abkuerzung, p.name, IFNULL(ss.typ,'Standard') AS typ, 
								IFNULL(ss.satz, (SELECT satz 
																 FROM stundensatz
                								 WHERE typ='Standard' AND adresse='$id'
                								 ORDER BY datum DESC LIMIT 1)) AS satz,
								IFNULL(ss.id,CONCAT('&projekt=',p.id)) AS ssid
								FROM adresse_rolle ar
								LEFT JOIN projekt as p
								ON ar.parameter=p.id
								LEFT JOIN (SELECT * FROM stundensatz AS dss ORDER BY dss.datum DESC) AS ss
								ON p.id=ss.projekt AND ss.adresse=ar.adresse ";
				$where = " ar.adresse='$id' AND subjekt='Mitarbeiter' AND objekt='Projekt' GROUP BY p.id ";
				$count = "SELECT COUNT(parameter) FROM adresse_rolle WHERE adresse='$id' AND subjekt='Mitarbeiter' AND objekt='Projekt'";
				$menu = "<a href=\"index.php?module=adresse&action=stundensatzedit&user=$id&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
			"&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=adresse&action=stundensatzdelete&user=$id&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>";
				$moreinfo=false;
			break;

			case "adresselohn":
				$heading = array('Monat','Jahr','Stunden','Men&uuml;');
				$width = array('20%','20%','20%','40%');
				$findcols = array('monat','jahr','stunden');
				$searchsql = array('monat','jahr');
				$sql = "SELECT SQL_CALC_FOUND_ROWS id,MONTHNAME(von) AS monat, YEAR(von) AS jahr, 
								SUM(ROUND((UNIX_TIMESTAMP(bis) - UNIX_TIMESTAMP(von))/3600,2)) as stunden
								FROM zeiterfassung ";
				$where = " adresse='$id' GROUP BY monat,jahr ORDER BY STR_TO_DATE(CONCAT(MONTH(von),',',YEAR(von)), '%m,%Y') ";
				//$where = " adresse='$id' GROUP BY monat,jahr ORDER BY STR_TO_DATE(CONCAT(MONTH(von),',',YEAR(von)), '%m,%Y') ";
				$count = "SELECT SQL_CALC_FOUND_ROWS id,MONTHNAME(von) AS monat, YEAR(von) AS jahr, 
									SUM(ROUND((UNIX_TIMESTAMP(bis) - UNIX_TIMESTAMP(von))/3600,2)) as stunden
									FROM zeiterfassung WHERE adresse='$id' GROUP BY monat,jahr ORDER BY STR_TO_DATE(CONCAT(MONTH(von),',',YEAR(von)), '%m,%Y');
									SELECT FOUND_ROWS();";
				$menu = "test";
	
			break;		

			case "backuplist":
				$heading = array('Name','Dateiname','Datum','Men&uuml;');
				$width = array('30%','30%','20%','20%');
				$findcols = array('name','dateiname','datum','id');
				$searchsql = array('name','datum');
				$sql = "SELECT SQL_CALC_FOUND_ROWS id, name, dateiname, datum, id as menu FROM backup";
        $defaultorder = 4;  //Optional wenn andere Reihenfolge gewuenscht

				$where = "";
				$count = "SELECT COUNT(id) FROM backup";
				$menu = "<a href=\"#\" onclick=BackupDialog(\"index.php?module=backup&action=recover&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
								"<a href=\"#\" onclick=DeleteDialog(\"index.php?module=backup&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>";
			break;

	    case "projekttabelle":
	      // headings
	      $heading =  array('Name','Abkuerzung','Verantwortlicher','Men&uuml;');
	      $width   =  array('30%','20%','20%','20%');
	      $findcols = array('name','abkuerzung','verantwortlicher','id');
	      $searchsql = array('name','abkuerzung','verantwortlicher');


	      $menu =  "<a href=\"index.php?module=projekt&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=projekt&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=projekt&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS id, name, abkuerzung, verantwortlicher, id as menu FROM projekt ";

	      // fester filter
	      $where = " geloescht=0 AND id!=0";

	      $count = "SELECT COUNT(id) FROM projekt WHERE geloescht=0";

	    break;
   

	    case "emailtabelle":
	      // headings
	      $heading =  array('','Datum','Absender','Betreff','Men&uuml;');
	      $width   =  array('1%','14%','35%','45%','5%');
	      $findcols = array('open','eingang','sender','subject','id');
	      $searchsql = array('e.sender','e.subject','e.empfang','e.action');


	      $menu =  "<!--<a href=\"index.php?module=email&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=email&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=email&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>-->";


	      $adresse = $this->app->User->GetAdresse();
    
	      $sql = "SELECT SQL_CALC_FOUND_ROWS  e.id, '<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(e.empfang, '%Y-%m-%d %H:%i' ) as  eingang, e.sender,e.subject,
		      e.id as menu FROM  emailbackup_mails e";
//            ORDER BY empfang DESC";

	      // SQL statement
//	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.name as name,a.kundennummer as kundennummer, 
//		    if(a.lieferantennummer,a.lieferantennummer,'-') as lieferantennummer, a.plz as plz, a.ort as ort, a.email as email, p.abkuerzung as projekt, a.id as menu
//		  FROM  adresse a LEFT JOIN projekt p ON p.id=a.projekt ";

	      // fester filter
	      $where = "e.webmail IN (SELECT eb.id FROM emailbackup eb WHERE eb.adresse = '$adresse') AND e.spam!='1' ";

	      $count = "SELECT COUNT(id) FROM emailbackup_mails WHERE webmail IN (SELECT id FROM emailbackup WHERE emailbackup.adresse = '$adresse') AND spam!='1' ";

              $menucol=4;
	      $moreinfo = true;


	    break;
   

	    case "adressetabelle":
	      // headings
	      $heading =  array('Name','Kunde','Lieferant','PLZ','Ort','E-Mail','Projekt','Men&uuml;');
	      $width   =  array('20%','5%','5%','5%','5%','5%','15%','10%');
	      $findcols = array('name','kundennummer','lieferantennummer','plz','ort','email','projekt','id');
	      $searchsql = array('a.ort','a.name','p.abkuerzung','a.plz','a.email','a.kundennummer','a.lieferantennummer');


	      $menu =  "<a href=\"index.php?module=adresse&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=adresse&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=adresse&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.name as name,if(a.kundennummer,a.kundennummer,'-') as kundennummer,
		    if(a.lieferantennummer,a.lieferantennummer,'-') as lieferantennummer, a.plz as plz, a.ort as ort, a.email as email, p.abkuerzung as projekt, a.id as menu
		  FROM  adresse a LEFT JOIN projekt p ON p.id=a.projekt ";

	      // fester filter
	      $where = "a.geloescht=0 ";

	      $count = "SELECT COUNT(id) FROM adresse WHERE geloescht=0";

	    break;
		   
	    case "artikeltabelleneu":                                                                                                                                                                  
              // headings                                                                                                                                                                                       
              $heading =  array('','Nummer','Artikel','Im Lager','Projekt','Men&uuml;');                                                                                                                                      
              $width   =  array('5%','10%','45%','8%','15%','10%');                                                                                                                                                       
              $findcols = array('nummer','name_de','projekt','id');                                                                                                                                             
              $searchsql = array('a.name_de','a.nummer','p.abkuerzung');                                                                                                                                        
                                                                                                                                                                                                                
	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
	  "<!--&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
	  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>-->";


              // SQL statement                                                                                                                                                                                  
              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, CONCAT('<input type=\"checkbox\" name=\"artikelmarkiert[]\" value=\"',a.id,'\">') as wahl, a.nummer as nummer, a.name_de as name_de, (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) as lager, p.abkuerzung as projekt, a.id as menu                                                                          
              FROM  artikel a LEFT JOIN projekt p ON p.id=a.projekt ";                                                                                                                                          
              // fester filter                                                                                                                                                                                  
              $where = "a.geloescht=0 AND a.neu='1' AND a.shop >0"; 
              $count = "SELECT COUNT(a.id) FROM artikel a WHERE a.geloescht=0 AND a.shop > 0 AND a.neu=1";                                                                                                                                       
            break;                                                                                                                                                       
 

   
	    case "artikeltabellehinweisausverkauft":                                                                                                                                                                  
              // headings                                                                                                                                                                                       
              $heading =  array('','Nummer','Artikel','Im Lager','Projekt','Men&uuml;');                                                                                                                                      
              $width   =  array('5%','10%','45%','8%','15%','10%');                                                                                                                                                       
              $findcols = array('nummer','name_de','projekt','id');                                                                                                                                             
              $searchsql = array('a.name_de','a.nummer','p.abkuerzung');                                                                                                                                        
                                                                                                                                                                                                                
	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
	  "<!--&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
	  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>-->";


              // SQL statement                                                                                                                                                                                  
              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, CONCAT('<input type=\"checkbox\" name=\"artikelmarkiert[]\" value=\"',a.id,'\">') as wahl, a.nummer as nummer, a.name_de as name_de, (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) as lager, p.abkuerzung as projekt, a.id as menu                                                                          
              FROM  artikel a LEFT JOIN projekt p ON p.id=a.projekt ";                                                                                                                                          
              // fester filter                                                                                                                                                                                  
              $where = "a.geloescht=0 AND (a.ausverkauft='1' OR a.gesperrt=1) AND a.shop > 0 AND (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) > 0"; 
              $count = "SELECT COUNT(a.id) FROM artikel a WHERE a.geloescht=0 AND a.shop > 0 AND (a.ausverkauft=1 OR a.gesperrt=1) AND (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) > 0";                                                                                                                                       
            break;                                                                                                                                                       
 


           case "artikeltabellelagerndabernichtlagernd":                                                                                                                                                                  
              // headings                                                                                                                                                                                       
              $heading =  array('','Nummer','Artikel','Im Lager','Projekt','Men&uuml;');                                                                                                                                      
              $width   =  array('5%','10%','45%','8%','15%','10%');                                                                                                                                                       
              $findcols = array('nummer','name_de','projekt','id');                                                                                                                                             
              $searchsql = array('a.name_de','a.nummer','p.abkuerzung');                                                                                                                                        
                                                                                                                                                                                                                
	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
	  "<!--&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
	  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>-->";


              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, CONCAT('<input type=\"checkbox\" name=\"artikelmarkiert[]\" value=\"',a.id,'\">') as wahl, a.nummer as nummer, a.name_de as name_de, (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) as lager, p.abkuerzung as projekt, a.id as menu                                                                          
              FROM  artikel a LEFT JOIN projekt p ON p.id=a.projekt ";                                                                                                                                          
              
              $where = "a.geloescht=0 AND (a.lieferzeit='lager' || a.lieferzeit='') AND a.lagerartikel=1  AND (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) IS NULL 
              		AND a.shop!=0 AND a.gesperrt=0 AND a.ausverkauft!='1' AND a.inaktiv!='1'"; 
              		
              $count = "SELECT COUNT(a.id) FROM artikel a WHERE a.geloescht=0 AND (a.lieferzeit='lager' || a.lieferzeit='') AND (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) IS NULL 
              	AND a.shop!=0 AND a.gesperrt=0 AND a.ausverkauft!='1' AND a.inaktiv!='1'";                                                                                                                                       
            break;                                                                                                                                                       
 

           case "artikeltabellenichtlagernd":                                                                                                                                                                  
              // headings                                                                                                                                                                                       
              $heading =  array('','Nummer','Artikel','Im Lager','Projekt','Men&uuml;');                                                                                                                                      
              $width   =  array('5%','10%','45%','8%','15%','10%');                                                                                                                                                       
              $findcols = array('nummer','name_de','projekt','id');                                                                                                                                             
              $searchsql = array('a.name_de','a.nummer','p.abkuerzung');                                                                                                                                        
                                                                                                                                                                                                                
	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
	  "<!--&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
	  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>-->";


              // SQL statement                                                                                                                                                                                  
              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,  CONCAT('<input type=\"checkbox\" name=\"artikelmarkiert[]\" value=\"',a.id,'\">') as wahl, a.nummer as nummer, a.name_de as name_de, (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) as lager, p.abkuerzung as projekt, a.id as menu                                                                          
              FROM  artikel a LEFT JOIN projekt p ON p.id=a.projekt ";                                                                                                                                          
              // fester filter                                                                                                                                                                                  
              $where = "a.geloescht=0 AND a.shop > 0 AND (a.lieferzeit!='lager' && a.lieferzeit!='') AND a.lagerartikel=1 "; 
              $count = "SELECT COUNT(id) FROM artikel WHERE geloescht=0 AND shop > 0 AND (lieferzeit!='lager' && lieferzeit!='')";                                                                                                                                       
            break;                                                                                                                                                       
 
	    case "artikeltabelle":
	      // headings
	      $heading =  array('Nummer','Artikel','Lagerbestand','Projekt','Men&uuml;');
	      $width   =  array('10%','60%','5%','15%','10%');
	      $findcols = array('nummer','name_de','lagerbestand','projekt','id');
	      $searchsql = array('a.name_de','a.nummer','p.abkuerzung');


	      $menu =  "<a href=\"index.php?module=artikel&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=copy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.nummer as nummer, a.name_de as name_de, (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) as lagerbestand,  p.abkuerzung as projekt, a.id as menu
		  FROM  artikel a LEFT JOIN projekt p ON p.id=a.projekt ";

	      // fester filter
	      $where = "a.geloescht=0 ";

	      $count = "SELECT COUNT(id) FROM artikel WHERE geloescht=0";

	    break;
   
 
	    case "stueckliste":
	      // headings
	      $heading =  array('Artikel','Nummer','Menge','Lager','Men&uuml;');
	      $width   =  array('70%','10%','5%','5%','10%');
	      $findcols = array('artikel','nummer','menge','id');
	      $searchsql = array('a.name_de','a.nummer','s.menge');


	     $menu = "<a href=\"index.php?module=artikel&action=editstueckliste&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=delstueckliste&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=einkaufcopy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS s.id, a.name_de as artikel,a.nummer as nummer, s.menge as menge, if((SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id) > 0,
	      (SELECT SUM(l.menge) FROM lager_platz_inhalt l WHERE l.artikel=a.id),0)  as lager, s.id as menu
		  FROM stueckliste s LEFT JOIN artikel a ON s.artikel=a.id  ";
	      
	      // Fester filter
	      $where = "s.stuecklistevonartikel='$id' ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(s.id) FROM stueckliste s WHERE s.stuecklistevonartikel='$id' ";


	    break;


	    case "lieferscheineinbearbeitung":
	      $heading =  array('','Vom','Kunde','Land','Projekt','Versand','Art','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','versandart','art','status','id');
	      $searchsql = array('l.id','l.datum','l.belegnr','adr.kundennummer','l.name','l.land','p.abkuerzung','l.status','l.plz','l.id');


              $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=lieferschein&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=lieferschein&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=lieferschein&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

              $menucol=8;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS l.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(l.datum,'%Y-%m-%d') as vom, 
	      l.name as name,  l.land as land, p.abkuerzung as projekt, l.versandart as versandart,  
	      l.lieferscheinart as art, UPPER(l.status) as status, l.id
		  FROM  lieferschein l LEFT JOIN projekt p ON p.id=l.projekt LEFT JOIN adresse adr ON l.adresse=adr.id  ";
	      // Fester filter

              $where = " ( l.status='angelegt' OR l.belegnr=0) ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(l.id) FROM lieferschein l WHERE ( l.status='angelegt' OR l.belegnr=0)";

	      $moreinfo = true;
              
	    break;
	

	    case "lieferscheineoffene":
	      $heading =  array('','Lieferschein','Vom','Kunde','Land','Projekt','Versand','Art','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','35%','5%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','belegnr','vom','name','land','projekt','versandart','art','status','id');
              $searchsql = array('l.id','l.datum','l.belegnr','adr.kundennummer','l.name','l.land','p.abkuerzung','l.status','l.plz','l.id');                                                                   
	                    


              $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=lieferschein&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=lieferschein&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=lieferschein&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

              $menucol=9;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS l.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, l.belegnr, DATE_FORMAT(l.datum,'%Y-%m-%d') as vom, 
	      l.name as name, l.land as land, p.abkuerzung as projekt, l.versandart as versandart,  
	      l.lieferscheinart as art, UPPER(l.status) as status, l.id
		  FROM  lieferschein l LEFT JOIN projekt p ON p.id=l.projekt LEFT JOIN adresse adr ON l.adresse=adr.id  ";
	      // Fester filter

              $where = " l.id!='' AND l.status='freigegeben' ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(l.id) FROM lieferschein l WHERE l.status='freigegeben'";

	      $moreinfo = true;
	    break;


	    case "kontoauszuege":

	      // headings
	      $heading =  array('','Vom','Vorgang','SOLL','HABEN','Gebuehr','Abgeschlossen','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','vorgang','soll','haben','gebuehr','fertig','id');
	      $searchsql = array('k.id','k.buchung','k.vorgang','k.soll','k.haben');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=zahlungseingang&action=editzeile&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=zahlungseingang&action=editzeile&cmd=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "</td></tr></table>";

	      $menucol=7;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS k.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(k.buchung,'%Y-%m-%d') as vom, 
	      k.vorgang, k.soll, k.haben, k.gebuehr,  
	       if(k.fertig=1,'abgeschlossen','pr&uuml;fen') as fertig, k.id
		  FROM  kontoauszuege k ";
	      // Fester filter


	      $where = " k.konto='$id' ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(k.id) FROM kontoauszuege k WHERE k.konto='$id'";

	      $moreinfo = true;

	    break;



	    case "lieferscheine":
				// START EXTRA checkboxen
        $this->app->Tpl->Add(JQUERYREADY,"$('#lieferscheinoffen').click( function() { fnFilterColumn1( 0 ); } );");
        $this->app->Tpl->Add(JQUERYREADY,"$('#lieferscheinheute').click( function() { fnFilterColumn2( 0 ); } );");
			
				for($r=1;$r<3;$r++)
    {
      $this->app->Tpl->Add(JAVASCRIPT,'
        function fnFilterColumn'.$r.' ( i )
        {
          if(oMoreData'.$r.$name.'==1)
          oMoreData'.$r.$name.' = 0;
          else
          oMoreData'.$r.$name.' = 1;

          $(\'#'.$name.'\').dataTable().fnFilter( 
          \'A\',
          i, 
          0,0
          );
        }
      ');
    }
        // ENDE EXTRA checkboxen

	      // headings
	      $heading =  array('','Lieferschein','Vom','Kunde','Land','Projekt','Versand','Art','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','35%','5%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','versandart','art','status','id');
	      $searchsql = array('l.id','l.datum','l.belegnr','adr.kundennummer','l.name','l.land','p.abkuerzung','l.status','l.plz','l.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=lieferschein&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=lieferschein&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=paketmarke&action=create&frame=false&sid=lieferschein&id=%value%\" class=\"popup\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/stamp.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=lieferschein&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=9;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS l.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, l.belegnr, DATE_FORMAT(l.datum,'%Y-%m-%d') as vom, 
	      l.name as name, l.land as land, p.abkuerzung as projekt, l.versandart as versandart,  
	      l.lieferscheinart as art, UPPER(l.status) as status, l.id
		  FROM  lieferschein l LEFT JOIN projekt p ON p.id=l.projekt LEFT JOIN adresse adr ON l.adresse=adr.id  ";
	      // Fester filter

				// START EXTRA more
        $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " l.status='freigegeben' ";
        $more_data2 = $this->app->Secure->GetGET("more_data2"); if($more_data2==1) $subwhere[] = " l.datum=CURDATE() ";
				// ENDE EXTRA more

				for($j=0;$j<count($subwhere);$j++)
          $tmp .=  " AND ".$subwhere[$j];


	      $where = " l.id!='' $tmp";

	      // gesamt anzahl
	      $count = "SELECT COUNT(l.id) FROM lieferschein l";

	      $moreinfo = true;

	    break;


	    case "gutschrifteninbearbeitung":
	      $heading =  array('','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=gutschrift&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=gutschrift&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=gutschrift&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=9;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name,  r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  gutschrift r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter

              $where = " ( r.status='angelegt' OR r.belegnr=0) ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(r.id) FROM gutschrift r WHERE ( r.status='angelegt' OR r.belegnr=0) ";

	      $moreinfo = true;

	    break;
	

	    case "gutschriftenoffene":
	      $heading =  array('','Vom','Kunde','Gutschrift','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','35%','5%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=gutschrift&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=gutschrift&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=gutschrift&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name,  r.belegnr, r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  gutschrift r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter


	      $where = " r.id!='' AND r.status='freigegeben'";
              
              // gesamt anzahl
	      $count = "SELECT COUNT(r.id) FROM gutschrift r";

	      $moreinfo = true;
	    break;


	    case "gutschriften":

	      $heading =  array('','Vom','Kunde','Gutschrift','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');

	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=gutschrift&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=gutschrift&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=gutschrift&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name, r.belegnr, r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  gutschrift r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";


              //if($tmp!="")$tmp .= " AND r.belegnr!='' ";

              $where = " r.id!='' ";


	      // gesamt anzahl
              $count = "SELECT COUNT(r.id) FROM gutschrift r WHERE r.status='freigegeben'";

	      $moreinfo = true;
/*
	      // headings
	      $heading =  array('','Vom','Kunde','Gutschrift','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');

	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=gutschrift&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=gutschrift&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=gutschrift&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name, r.belegnr, r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  gutschrift r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter


              $where = " r.id!='' AND r.status='freigegeben' ";

	      // gesamt anzahl
              $count = "SELECT COUNT(r.id) FROM gutschrift r WHERE r.status='freigegeben'";

	      $moreinfo = true;
*/
	    break;





	    case "rechnungeninbearbeitung":
	      $heading =  array('','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=rechnung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=rechnung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=rechnung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=9;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name,  r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  rechnung r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter

              $where = " ( r.status='angelegt' OR r.belegnr=0) ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(r.id) FROM rechnung r WHERE ( r.status='angelegt' OR r.belegnr=0) ";

	      $moreinfo = true;

	    break;
	

	    case "rechnungenoffene":
	      $heading =  array('','Rechnung','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','35%','5%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=rechnung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=rechnung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=rechnung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, r.belegnr, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name,  r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  rechnung r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter


              $where = " r.id!='' AND r.status='freigegeben' ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(r.id) FROM rechnung r WHERE r.status='freigegeben'";

	      $moreinfo = true;
	    break;


	    case "rechnungen":

	      $this->app->Tpl->Add(JQUERYREADY,"$('#zahlungseingang').click( function() { fnFilterColumn1( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#zahlungseingangfehlt').click( function() { fnFilterColumn2( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#rechnungenheute').click( function() { fnFilterColumn3( 0 ); } );");


		for($r=1;$r<4;$r++)
		{
		  $this->app->Tpl->Add(JAVASCRIPT,'
				function fnFilterColumn'.$r.' ( i )
				{
					if(oMoreData'.$r.$name.'==1)
					oMoreData'.$r.$name.' = 0;
					else
					oMoreData'.$r.$name.' = 1;

					$(\'#'.$name.'\').dataTable().fnFilter( 
					\'A\',
					i, 
					0,0
					);
				}
			');
		}


	      // headings
	      $heading =  array('','Rechnung','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Zahlung','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','35%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','zahlung','status','icons','id');
	      $searchsql = array('r.id','r.datum','r.belegnr','adr.kundennummer','r.name','r.land','p.abkuerzung','r.zahlungsweise','r.status','r.soll','r.ist','r.zahlungsstatus','r.plz','r.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=rechnung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=rechnung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=rechnung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS r.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, r.belegnr, DATE_FORMAT(r.datum,'%Y-%m-%d') as vom, 
	      r.name as name, r.land as land, p.abkuerzung as projekt, r.zahlungsweise as zahlungsweise,  
	      r.soll as soll, r.zahlungsstatus as zahlung, UPPER(r.status) as status, r.id
		  FROM  rechnung r LEFT JOIN projekt p ON p.id=r.projekt LEFT JOIN adresse adr ON r.adresse=adr.id  ";
	      // Fester filter

	      $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " r.zahlungsstatus='bezahlt' ";
        $more_data2 = $this->app->Secure->GetGET("more_data2"); if($more_data2==1) $subwhere[] = " r.zahlungsstatus!='bezahlt' ";
        $more_data3 = $this->app->Secure->GetGET("more_data3"); if($more_data3==1) { $subwhere[] = " r.datum=CURDATE() "; $ignore=true; }

        for($j=0;$j<count($subwhere);$j++)
        	$tmp .=  " AND ".$subwhere[$j];

        if($tmp!="" && !$ignore)$tmp .= " AND r.belegnr!='' ";

        $where = " r.id!='' $tmp";


	      // gesamt anzahl
	      $count = "SELECT COUNT(r.id) FROM rechnung r";

	      $moreinfo = true;

	    break;



	    case "bestellungeninbearbeitung":
	      $heading =  array('','Vom','Kunde','Land','Projekt','Betrag','Status','Men&uuml;');
              $width   =  array('1%','1%','50%','1%','1%','1%','1%','1%','1%','1%');
              $findcols = array('open','vom','name','land','projekt','betrag','status','icons','id');
              $searchsql = array('a.id','a.datum','a.belegnr','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


              $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=bestellung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=bestellung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=bestellung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

              $menucol=7;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS b.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(b.datum,'%Y-%m-%d') as vom, 
	      b.name as name,  b.land as land, p.abkuerzung as projekt, 
	      b.gesamtsumme as summe, UPPER(b.status) as status, b.id
		  FROM  bestellung b LEFT JOIN projekt p ON p.id=b.projekt LEFT JOIN adresse adr ON b.adresse=adr.id  ";
	      // Fester filter

              
              $where = " ( b.status='angelegt' OR b.belegnr=0) ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(b.id) FROM bestellung b WHERE ( b.status='angelegt' OR b.belegnr=0) ";

	      $moreinfo = true;

	    break;
	

	    case "bestellungenoffene":

	      // headings
	      $heading =  array('','Vom','Kunde','Land','Projekt','Betrag','Status','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
              $findcols = array('open','vom','name','land','projekt','betrag','status','icons','id');
              $searchsql = array('a.id','a.datum','a.belegnr','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=bestellung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=bestellung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=bestellung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=7;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS b.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(b.datum,'%Y-%m-%d') as vom, 
	      b.name as name,  b.land as land, p.abkuerzung as projekt,  
	      b.gesamtsumme as summe, UPPER(b.status) as status, b.id
		  FROM  bestellung b LEFT JOIN projekt p ON p.id=b.projekt LEFT JOIN adresse adr ON b.adresse=adr.id  ";
	      // Fester filter

	      $where = " b.id!='' ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(b.id) FROM bestellung b";

	      $moreinfo = true;

              // gesamt anzahl
              $count = "SELECT COUNT(b.id) FROM bestellung b WHERE b.status='freigegeben'";

	      $moreinfo = true;
	    break;


	    //offene

	    case "bestellungen":
				// START EXTRA checkboxen
        $this->app->Tpl->Add(JQUERYREADY,"$('#bestellungenoffen').click( function() { fnFilterColumn1( 0 ); } );");

    for($r=1;$r<2;$r++)
    {
      $this->app->Tpl->Add(JAVASCRIPT,'
        function fnFilterColumn'.$r.' ( i )
        {
          if(oMoreData'.$r.$name.'==1)
          oMoreData'.$r.$name.' = 0;
          else
          oMoreData'.$r.$name.' = 1;

          $(\'#'.$name.'\').dataTable().fnFilter( 
          \'A\',
          i, 
          0,0
          );
        }
      ');
    }
        // ENDE EXTRA checkboxen

	      // headings
	      $heading =  array('','Bestellung','Vom','Kunde','Land','Projekt','Betrag','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','50%','1%','1%','1%','1%','1%','1%');
              $findcols = array('open','belegnr','vom','name','land','projekt','summe','status','icons','id');
              $searchsql = array('b.id','FORMAT(b.datum,\'%d.%m.%Y\')','b.belegnr','adr.kundennummer','b.name','b.land','p.abkuerzung','b.zahlungsweise','b.status','b.gesamtsumme','b.plz','b.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=bestellung&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=bestellung&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=bestellung&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=8;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS b.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, b.belegnr as belegnr, DATE_FORMAT(b.datum,'%Y-%m-%d') as vom, 
	      b.name as name,  b.land as land, p.abkuerzung as projekt,
	      b.gesamtsumme as summe, UPPER(b.status) as status, b.id
		  FROM  bestellung b LEFT JOIN projekt p ON p.id=b.projekt LEFT JOIN adresse adr ON b.adresse=adr.id  ";
	      // Fester filter

			
        // START EXTRA more
				// TODO: status abgeschlossen muss noch umgesetzt werden
        $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " b.status!='abgeschlossen' ";
				for($j=0;$j<count($subwhere);$j++)
          $tmp .=  " AND ".$subwhere[$j];
        // START EXTRA more

	      $where = " b.id!='' $tmp";

	      // gesamt anzahl
	      $count = "SELECT COUNT(b.id) FROM bestellung b";

	      $moreinfo = true;

	    break;

			case "belege":

				$id = $this->app->Secure->GetGET('id');
				$heading =  array('Datum','Beleg','Status','Land','Projekt','Typ','Men&uuml;');
				$width = 		array('15%','10%','20%','5%','20%','15%','15%');
        $findcols = array('datum','belegnr','status','land','projekt','typ','link');
				$searchsql = array('datum','belegnr','status','land','projekt','typ');
				$menu = "<a href=\"%value%&action=edit\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>&nbsp;"
							 ."<a href=\"%value%&action=pdf\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a>&nbsp;"
							 ."<a href=\"#\" onclick=DeleteDialog(\"%value%&action=delete\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>";
				$sql = "SELECT id, datum, belegnr, status, land, projekt, UPPER(typ), CONCAT('index.php?module=',typ,'&id=',id) as link FROM belege ";
				$where = " adresse='$id' ";
				$count = "SELECT COUNT(id) FROM belege WHERE adresse='$id'";
				$moreinfo=false;
			break; 


	    case "angeboteinbearbeitung":
	      $heading =  array('','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Status','Men&uuml;');
              $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%','1%');
              $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','status','icons','id');
              $searchsql = array('a.id','a.datum','a.belegnr','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


              $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=angebot&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=angebot&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=angebot&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

              $menucol=8;
              // SQL statement
              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, DATE_FORMAT(a.datum,'%Y-%m-%d') as vom, 
              a.name as name,  a.land as land, p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise,
              a.gesamtsumme as betrag, UPPER(a.status) as status, a.id
                  FROM  angebot a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
              // Fester filter
              

              $where = " ( a.status='angelegt' OR a.belegnr=0) ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(a.id) FROM angebot a WHERE ( a.status='angelegt' OR a.belegnr=0) ";

	      $moreinfo = true;

	    break;
	

	    case "angeboteoffene":
              $heading =  array('','Angebot','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Status','Men&uuml;');
              $width   =  array('1%','1%','1%','35%','1%','1%','1%','1%','1%','1%');
              $findcols = array('open','belegnr','vom','name','land','projekt','zahlungsweise','betrag','status','icons','id');
              $searchsql = array('a.id','a.datum','a.belegnr','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


              $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=angebot&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
                  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=angebot&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
                  "&nbsp;<a href=\"index.php?module=angebot&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

              $menucol=9;
              // SQL statement
              $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, a.belegnr, DATE_FORMAT(a.datum,'%Y-%m-%d') as vom, 
              a.name as name,  a.land as land, p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise,
              a.gesamtsumme as betrag, UPPER(a.status) as status, a.id
                  FROM  angebot a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
              // Fester filter
              

              $where = " a.id!='' AND a.status='freigegeben' ";
              
              // gesamt anzahl
              $count = "SELECT COUNT(a.id) FROM angebot a WHERE a.status='freigegeben'";

	      $moreinfo = true;
	    break;


	    case "angebote":

				// START EXTRA checkboxen
        $this->app->Tpl->Add(JQUERYREADY,"$('#angeboteoffen').click( function() { fnFilterColumn1( 0 ); } );");
        $this->app->Tpl->Add(JQUERYREADY,"$('#angeboteheute').click( function() { fnFilterColumn2( 0 ); } );");

    		for($r=1;$r<3;$r++)
    		{
      		$this->app->Tpl->Add(JAVASCRIPT,'
        	function fnFilterColumn'.$r.' ( i )
        	{
          	if(oMoreData'.$r.$name.'==1)
          		oMoreData'.$r.$name.' = 0;
          	else
          		oMoreData'.$r.$name.' = 1;

          $(\'#'.$name.'\').dataTable().fnFilter( 
          \'A\',
          i, 
          0,0
          );
        	}
      	');
    	 }
        // ENDE EXTRA checkboxen

	      // headings
	      $heading =  array('','Angebot','Vom','Kunde','Angebot','Land','Projekt','Zahlung','Betrag','Status','Men&uuml;');
	      $width   =  array('1%','1%','1%','45%','5%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','belegnr','vom','name','land','projekt','zahlungsweise','betrag','status','icons','id');
	      $searchsql = array('a.id','a.datum','a.belegnr','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=angebot&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=angebot&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=angebot&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=9;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, a.belegnr as belegnr, DATE_FORMAT(a.datum,'%Y-%m-%d') as vom, 
	      a.name as name, a.belegnr, a.land as land, p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise,  
	      a.gesamtsumme as betrag, UPPER(a.status) as status, a.id
		  FROM  angebot a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
	      // Fester filter

        // START EXTRA more

        $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " a.status='freigegeben' ";
        $more_data2 = $this->app->Secure->GetGET("more_data2"); if($more_data2==1) $subwhere[] = " a.datum=CURDATE() AND a.status='freigegeben'";


        for($j=0;$j<count($subwhere);$j++)
          $tmp .=  " AND ".$subwhere[$j];

	      $where = " a.id!='' $tmp";

	      // gesamt anzahl
	      $count = "SELECT COUNT(a.id) FROM angebot a";

	      $moreinfo = true;

	    break;


	    case "auftraegeinbearbeitung":

	      // headings
	      $heading =  array('','Vom','Kunde','Land','Projekt','Zahlung','Betrag','Monitor','Men&uuml;');
	      $width   =  array('1%','1%','40%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','vom','name','land','projekt','zahlungsweise','betrag','status','id');
	      $searchsql = array('a.id','a.datum','a.belegnr','internet','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.gesamtsumme','a.status','a.plz','a.id');

	      $defaultorder = 3; 

	     $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=auftrag&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=auftrag&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=auftrag&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=8;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png' as open,  a.datum as vom, 
	      a.name as name, a.land as land, p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise, a.gesamtsumme as betrag, (".$this->IconsSQL().") as icons, a.id
		  FROM  auftrag a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
	      // Fester filter
	      $where = " ( a.status='angelegt' OR a.belegnr=0) ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(a.id) FROM auftrag a WHERE ( a.status='angelegt' OR a.belegnr=0) ";

	      $moreinfo = true;

	    break;
	


	    case "auftraegeoffene":

	      // headings
	      $heading =  array('','Auftrag','Kunde','Vom','Land','Projekt','Zahlung','Betrag','S','Monitor','Men&uuml;');
	      $width   =  array('1%','5%','35%','1%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','belegnr','name','vom','land','projekt','zahlungsweise','betrag','status','icons','id');
	      $searchsql = array('a.id','DATE_FORMAT(a.datum,\'%d.%m.%Y\')','a.belegnr','internet','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


	     $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=auftrag&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=auftrag&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=auftrag&action=pdf&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=10;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, a.belegnr, a.name as name, a.datum as vom, 
	      IF(a.internet !='', CONCAT(a.land,' (I)'), a.land) as land, 
	      p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise, a.gesamtsumme as betrag, UPPER(SUBSTRING(a.status,1,1)) as status,  (".$this->IconsSQL().")  as icons, a.id 
		  FROM  auftrag a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id ";
	      // Fester filter
	      $where = " a.id!='' AND a.belegnr!=0 AND a.autoversand='0' AND a.status='freigegeben' AND a.inbearbeitung=0 ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(a.id) FROM auftrag a WHERE a.belegnr!=0 AND a.lager_ok='1' AND a.vorkasse_ok='1' AND a.check_ok='1' AND a.status='freigegeben' AND a.inbearbeitung=0 AND a.nachlieferung!='1' AND a.ust_ok='1' ";

	      $moreinfo = true;

	    break;


	    case "auftraegeoffeneauto":

	      // headings
	      $heading =  array('','Versand','Auftrag','Kunde','Vom','Land','Projekt','Zahlung','Betrag','S','Monitor','Men&uuml;');
	      $width   =  array('1%','1%','5%','35%','1%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','versand','belegnr','name','vom','land','projekt','zahlungsweise','betrag','status','icons','id');
	      $searchsql = array('a.id','DATE_FORMAT(a.datum,\'%d.%m.%Y\')','a.belegnr','internet','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme','a.status','a.plz','a.id');


	     $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=auftrag&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=auftrag&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=auftrag&action=pdf&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=11;
	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, CONCAT('<input type=\"checkbox\" name=\"auftraegemarkiert[]\" value=\"',a.id,'\" checked>') as versand, a.belegnr, a.name as name, a.datum as vom, 
	      IF(a.internet !='', CONCAT(a.land,' (I)'), a.land) as land, 
	      p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise, a.gesamtsumme as betrag, UPPER(SUBSTRING(a.status,1,1)) as status,  (".$this->IconsSQL().")  as icons, a.id 
		  FROM  auftrag a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
	      // Fester filter
	      $where = " a.id!='' AND a.autoversand='1' AND a.belegnr!=0 AND a.lager_ok='1' AND a.vorkasse_ok='1' AND a.check_ok='1' AND a.status='freigegeben' AND a.inbearbeitung=0 AND a.nachlieferung!='1' AND a.ust_ok='1' ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(a.id) FROM auftrag a WHERE  a.id!='' AND a.autoversand='1' AND a.belegnr!=0 AND a.lager_ok='1' AND a.vorkasse_ok='1' AND a.check_ok='1' AND a.status='freigegeben' 
		AND a.inbearbeitung=0 AND a.nachlieferung!='1' AND a.ust_ok='1'  ";

	      $moreinfo = true;

	    break;


	    case "auftraege":
	      // START EXTRA checkboxen
	      $this->app->Tpl->Add(JQUERYREADY,"$('#artikellager').click( function() { fnFilterColumn1( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#ustpruefung').click( function() { fnFilterColumn2( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#zahlungseingang').click( function() { fnFilterColumn3( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#zahlungseingangfehlt').click( function() { fnFilterColumn5( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#manuellepruefung').click( function() { fnFilterColumn4( 0 ); } );");

	      $this->app->Tpl->Add(JQUERYREADY,"$('#auftragheute').click( function() { fnFilterColumn6( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#auftragoffene').click( function() { fnFilterColumn7( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#auftragstornierte').click( function() { fnFilterColumn8( 0 ); } );");
	      $this->app->Tpl->Add(JQUERYREADY,"$('#auftragabgeschlossene').click( function() { fnFilterColumn9( 0 ); } );");
//	      $this->app->Tpl->Add(JQUERYREADY,"$('#artikellager').click( function() {  oTable".$name.".fnDraw(); } );");


		for($r=1;$r<10;$r++)
		{
		  $this->app->Tpl->Add(JAVASCRIPT,'
				function fnFilterColumn'.$r.' ( i )
				{
					if(oMoreData'.$r.$name.'==1)
					oMoreData'.$r.$name.' = 0;
					else
					oMoreData'.$r.$name.' = 1;

					$(\'#'.$name.'\').dataTable().fnFilter( 
					\'A\',
					i, 
					0,0
					);
				}
			');
		}
	      // ENDE EXTRA checkboxen

	      // headings
	      $heading =  array('','Auftrag','Kd-Nr.','Kunde','Vom','Land','Projekt','Zahlung','Betrag','Status','Monitor','Men&uuml;');
	      $width   =  array('1%','1%','1%','35%','5%','1%','1%','1%','1%','1%','1%','1%');
	      $findcols = array('open','belegnr','kunde','name','vom','land','projekt','zahlungsweise','betrag','status','icons','id');
	      $searchsql = array('a.id','a.datum','a.belegnr','internet','adr.kundennummer','a.name','a.land','p.abkuerzung','a.zahlungsweise','a.status','a.gesamtsumme',
													 'a.status','a.plz','a.id');

	      $defaultorder = 3;  //Optional wenn andere Reihenfolge gewuenscht


	      $menu = "<table cellpadding=0 cellspacing=0><tr><td nowrap><a href=\"index.php?module=auftrag&action=edit&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=auftrag&action=delete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"index.php?module=auftrag&action=pdf&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/pdf.png\" border=\"0\"></a></td></tr></table>";

	      $menucol=11;

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS a.id,'<img src=/themes/{$this->app->Conf->WFconf[defaulttheme]}/images/details_open.png>' as open, a.belegnr, adr.kundennummer as kunde,a.name as name, DATE_FORMAT(a.datum,'%Y-%m-%d') as vom, 
	      IF(a.internet !='', CONCAT(a.land,' (I)'), a.land) as land, p.abkuerzung as projekt, a.zahlungsweise as zahlungsweise,  
	      a.gesamtsumme as betrag, LEFT(UPPER(a.status),7) as status,  (".$this->IconsSQL().")  as icons, a.id
		  FROM  auftrag a LEFT JOIN projekt p ON p.id=a.projekt LEFT JOIN adresse adr ON a.adresse=adr.id  ";
	      // Fester filter
	      
	      // START EXTRA more
	    
	      $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " a.lager_ok=0 ";
	      $more_data2 = $this->app->Secure->GetGET("more_data2"); if($more_data2==1) $subwhere[] = " a.ust_ok=0 ";
	      $more_data3 = $this->app->Secure->GetGET("more_data3"); if($more_data3==1) $subwhere[] = " a.vorkasse_ok=1 ";
	      $more_data4 = $this->app->Secure->GetGET("more_data4"); if($more_data4==1) $subwhere[] = " a.check_ok=0 ";
	      $more_data5 = $this->app->Secure->GetGET("more_data5"); if($more_data5==1) $subwhere[] = " a.vorkasse_ok=0 ";

				$more_data6 = $this->app->Secure->GetGET("more_data6"); if($more_data6==1) { $subwhere[] = " a.datum=CURDATE() "; $ignore = true; }
				$more_data7 = $this->app->Secure->GetGET("more_data7"); if($more_data7==1) { $subwhere[] = " a.status='freigegeben' "; $ignore = true; }
				$more_data8 = $this->app->Secure->GetGET("more_data8"); if($more_data8==1) { $subwhere[] = " a.status='storniert' "; $ignore = true; }
				$more_data9 = $this->app->Secure->GetGET("more_data9"); if($more_data9==1) { $subwhere[] = " a.status='abgeschlossen' "; $ignore = true; }


	      for($j=0;$j<count($subwhere);$j++)
					$tmp .=  " AND ".$subwhere[$j]; 

	      if($tmp!="" && !$ignore)$tmp .= " AND a.status='freigegeben' ";

	      // ENDE EXTRA more

	      $where = " a.id!='' $tmp";

	      // gesamt anzahl
	      $count = "SELECT COUNT(a.id) FROM auftrag a ";

	      $moreinfo = true; // EXTRA

	    break;


	    case "einkaufspreise":
    $this->app->Tpl->Add(JQUERYREADY,"$('#alteeinkaufspreise').click( function() { fnFilterColumn1( 0 ); } );");

    for($r=1;$r<2;$r++)
    {
      $this->app->Tpl->Add(JAVASCRIPT,'
        function fnFilterColumn'.$r.' ( i )
        {
          if(oMoreData'.$r.$name.'==1)
          oMoreData'.$r.$name.' = 0;
          else
          oMoreData'.$r.$name.' = 1;

          $(\'#'.$name.'\').dataTable().fnFilter( 
          \'A\',
          i, 
          0,0
          );
        }
      ');
    }  

	      // headings
	      $heading =  array('Lieferant','Bezeichnung','Bestellnummer','ab','VPE','Preis','W&auml;hrung','bis','Men&uuml;');
	      $width   =  array('35%','35%','3%','3%','1%','1%','1%','1%','20%');
	      $findcols = array('lieferant','bezeichnunglieferant','bestellnummer','ab_menge','vpe','preis','waehrung','gueltig_bis','id');
	      $searchsql = array('adr.name','e.bezeichnunglieferant','e.bestellnummer','e.ab_menge','e.vpe');


	     $menu = "<a href=\"index.php?module=artikel&action=einkaufeditpopup&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DisableDialog(\"index.php?module=artikel&action=einkaufdisable&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/proforma.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=einkaufdelete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=einkaufcopy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS e.id, adr.name as lieferant, e.bezeichnunglieferant, e.bestellnummer, 
		  e.ab_menge as ab_menge ,e.vpe as vpe,e.preis as preis,e.waehrung as waehrung, if(e.gueltig_bis='0000-00-00','-',e.gueltig_bis) as gueltig_bis, e.id as menu
		  FROM  einkaufspreise e LEFT JOIN projekt p ON p.id=e.projekt LEFT JOIN adresse adr ON e.adresse=adr.id  ";
	       
        $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " OR ( e.artikel='$id' AND e.gueltig_bis !='0000-00-00' AND e.gueltig_bis < NOW() AND e.geloescht=0)  ";

              for($j=0;$j<count($subwhere);$j++)
                $tmp .=  "  ".$subwhere[$j];

//              if($tmp!="")$tmp .= " AND e.geloescht='1' ";

	      // Fester filter
	      $where = "e.artikel='$id' AND e.geloescht='0' AND (e.gueltig_bis>NOW() OR e.gueltig_bis='0000-00-00') $tmp";

	      // Fester filter
//	      $where = "e.artikel='$id' AND e.geloescht='0' ";

	      // gesamt anzahl
	      $count = "SELECT COUNT(e.id) FROM einkaufspreise e WHERE e.artikel='$id'  AND e.geloescht='0'";

	    break;

 
	    case "verkaufspreise":
				  $this->app->Tpl->Add(JQUERYREADY,"$('#alteverkaufspreise').click( function() { fnFilterColumn1( 0 ); } );");


    for($r=1;$r<2;$r++)
    {
      $this->app->Tpl->Add(JAVASCRIPT,'
        function fnFilterColumn'.$r.' ( i )
        {
          if(oMoreData'.$r.$name.'==1)
          oMoreData'.$r.$name.' = 0;
          else
          oMoreData'.$r.$name.' = 1;

          $(\'#'.$name.'\').dataTable().fnFilter( 
          \'A\',
          i, 
          0,0
          );
        }
      ');
    }

	      // headings
	      $heading =  array('Kunde','ab','Preis','G&uuml;ltig bis','Men&uuml;');
	      $width   =  array('50%','10%','10%','10%','15%');
	      $findcols = array('kunde','ab_menge','gueltig_bis','preis','id');
	      $searchsql = array('adr.name','v.ab_menge','v.gueltig_bis','v.preis');

	      $menu = "<a href=\"index.php?module=artikel&action=verkaufeditpopup&id=%value%\"><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DisableDialog(\"index.php?module=artikel&action=verkaufdisable&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/proforma.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=DeleteDialog(\"index.php?module=artikel&action=verkaufdelete&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\"></a>".
		  "&nbsp;<a href=\"#\" onclick=CopyDialog(\"index.php?module=artikel&action=verkaufcopy&id=%value%\");><img src=\"themes/{$this->app->Conf->WFconf[defaulttheme]}/images/copy.png\" border=\"0\"></a>";

	      // SQL statement
	      $sql = "SELECT SQL_CALC_FOUND_ROWS v.id, if(v.adresse='' or v.adresse=0,'Alle',adr.name) as kunde,  v.ab_menge as ab_menge, v.preis as preis,v.gueltig_bis as gueltig_bis, v.id as menu
		  FROM  verkaufspreise v LEFT JOIN adresse adr ON v.adresse=adr.id  ";
	      
        $more_data1 = $this->app->Secure->GetGET("more_data1"); if($more_data1==1) $subwhere[] = " OR (v.gueltig_bis !='0000-00-00' AND v.gueltig_bis < NOW() AND v.geloescht='0' AND v.artikel='$id')";

              for($j=0;$j<count($subwhere);$j++)
                $tmp .=  "  ".$subwhere[$j];

//              if($tmp!="")$tmp .= " AND (v.gueltig_bis>NOW() OR v.gueltig_bis='0000-00-00') ";

	      // Fester filter
	      $where = "v.artikel='$id' AND v.geloescht='0' AND (v.gueltig_bis>NOW() OR v.gueltig_bis='0000-00-00') $tmp";

	      // gesamt anzahl
	      $count = "SELECT COUNT(v.id) FROM verkaufspreise v WHERE v.artikel='$id' AND v.geloescht='0'";

	    break;


	    default:
	      break;
	  }



	  if($callback=="show")
	  {

	    $this->app->Tpl->Add(ADDITIONALCSS,"

.ex_highlight #$name tbody tr.even:hover, #example tbody tr.even td.highlighted {
  background-color: #ECFFB3; 
}

.ex_highlight_row #$name tr.even:hover {
  background-color: #ECFFB3;
}

.ex_highlight_row #$name tr.even:hover td.sorting_1 {
  background-color: #DDFF75;
}

.ex_highlight_row #$name tr.odd:hover {
  background-color: #E6FF99;
}

.ex_highlight_row #$name tr.odd:hover td.sorting_1 {
  background-color: #D6FF5C;
}
");

		      //"sPaginationType": "full_numbers",
		  //"aLengthMenu": [[10, 25, 50, 10000], [10, 25, 50, "All"]],


$this->app->Tpl->Add(JAVASCRIPT," var oTable".$name."; var oMoreData1".$name."=0; var oMoreData2".$name."=0; var oMoreData3".$name."=0; var oMoreData4".$name."=0; var oMoreData5".$name."=0;  var oMoreData6".$name."=0; var oMoreData7".$name."=0; var oMoreData8".$name."=0; var oMoreData9".$name."=0; var aData;
  ");

$smodule = $this->app->Secure->GetGET("cmd");


  if($this->app->Secure->GetGET("module")=="artikel")
  {
    $sort = '"aaSorting": [[ 0, "desc" ]],';
  } else {
    $sort = '"aaSorting": [[ 1, "desc" ]],';
  }

	    $this->app->Tpl->Add(DATATABLES,
'
	     oTable'.$name.' = $(\'#'.$name.'\').dataTable( {
		  "bProcessing": true,
		  "aLengthMenu": [[10, 25, 50,200,1000], [10, 25, 50, 200,1000]],
		  "iDisplayLength": 10,
		      "bStateSave": true,
			'.$sort.'
		  "bServerSide": true,
    "fnServerData": function ( sSource, aoData, fnCallback ) {
      /* Add some extra data to the sender */
      aoData.push( { "name": "more_data1", "value": oMoreData1'.$name.' } );
      aoData.push( { "name": "more_data2", "value": oMoreData2'.$name.' } );
      aoData.push( { "name": "more_data3", "value": oMoreData3'.$name.' } );
      aoData.push( { "name": "more_data4", "value": oMoreData4'.$name.' } );
      aoData.push( { "name": "more_data5", "value": oMoreData5'.$name.' } );
      aoData.push( { "name": "more_data6", "value": oMoreData6'.$name.' } );
      aoData.push( { "name": "more_data7", "value": oMoreData7'.$name.' } );
      aoData.push( { "name": "more_data8", "value": oMoreData8'.$name.' } );
      aoData.push( { "name": "more_data9", "value": oMoreData9'.$name.' } );
      $.getJSON( sSource, aoData, function (json) { 
	/* Do whatever additional processing you want on the callback, then tell DataTables */
	fnCallback(json)
      } );
    },

		  "sAjaxSource": "./index.php?module=ajax&action=table&smodule='.$smodule.'&cmd='.$name.'&id='.$id.'"
		} );

	      ');
if($moreinfo)
{
$this->app->Tpl->Add(DATATABLES,
'
$(\'#'.$name.' tbody td img\').live( \'click\', function () {
    var nTr = this.parentNode.parentNode;
    aData =  oTable'.$name.'.fnGetData( nTr );

    if ( this.src.match(\'details_close\') )
    {
      /* This row is already open - close it */
      this.src = "./themes/'.$this->app->Conf->WFconf[defaulttheme].'/images/details_open.png";
      oTable'.$name.'.fnClose( nTr );
    }
    else
    {
      /* Open this row */
      this.src = "./themes/'.$this->app->Conf->WFconf[defaulttheme].'/images/details_close.png";
      oTable'.$name.'.fnOpen( nTr, '.$name.'fnFormatDetails(nTr), \'details\' );
    }
  });
');
/*  $.get("index.php?module=auftrag&action=minidetail&id=2", function(text){
    spin=0; 
    miniauftrag = text;
  });
*/

$module = $this->app->Secure->GetGET("module");

$this->app->Tpl->Add(JAVASCRIPT,'function '.$name.'fnFormatDetails ( nTr ) {
  //var aData =  oTable'.$name.'.fnGetData( nTr );
  var str = aData['.$menucol.'];
  var match = str.match(/[1-9]{1}[0-9]*/);

  var auftrag = parseInt(match[0], 10);

  var miniauftrag;
  var strUrl = "index.php?module='.$module.'&action=minidetail&id="+auftrag; //whatever URL you need to call
  var strReturn = "";

  jQuery.ajax({
    url:strUrl, success:function(html){strReturn = html;}, async:false
  });

  miniauftrag = strReturn;

  var sOut = \'<table cellpadding="0" cellspacing="0" border="0" align="center" style="padding-left: 30px;">\';
  sOut += \'<tr><td>\'+miniauftrag+\'</td></tr>\';
  sOut += \'</table>\';
  return sOut;
}
');
  


}



      $colspan = count($heading);

      $this->app->Tpl->Add($parsetarget,'
	<br><br>
	<table cellpadding="0" cellspacing="0" border="0" style="width:700" class="display" id="'.$name.'">
	  <thead>
	    <tr><th colspan="'.$colspan .'"><br></th></tr>
	    <tr>');

	for($i=0;$i<count($heading);$i++)
	{
	    $this->app->Tpl->Add($parsetarget,'<th width="'.$width[$i].'">'.$heading[$i].'</th>');
	}

      $this->app->Tpl->Add($parsetarget,'</tr>
	  </thead>
	  <tbody>
	    <tr>
	      <td colspan="'.$colspan .'" class="dataTables_empty">Lade Daten</td>
	    </tr>
	  </tbody>

	  <tfoot>
	    <tr>
	');


	for($i=0;$i<count($heading);$i++)
	{
	    $this->app->Tpl->Add($parsetarget,'<th>'.$heading[$i].'</th>');
	}


	$this->app->Tpl->Add($parsetarget,'
	    </tr>
	  </tfoot>
	</table>
	<br>
	<br>
	<br>
	');
	    } else if ($callback=="sql")
	      return $sql;
	    else if ($callback=="searchsql")
	      return $searchsql; 
	    else if ($callback=="defaultorder")
	      return $defaultorder; 
	    else if ($callback=="heading")
	      return $heading; 
	   else if ($callback=="menu")
	      return $menu; 
	  else if ($callback=="findcols")
	      return $findcols; 
	  else if ($callback=="where")
	      return $where; 
	  else if ($callback=="count")
	      return $count; 




  }



  function AutoComplete($fieldname,$filter,$onlyfirst=0,$extendurl="")
  {
		if($onlyfirst)
		{
			$tpl ='
							$( "#'.$fieldname.'" ).autocomplete({
							source: "index.php?module=ajax&action=filter&filtername='.$filter.$extendurl.'",
							select: function( event, ui ) {
								var i = ui.item.value;
								var zahl = i.indexOf(" ");
								var text = i.slice(0, zahl);
								$( "#'.$fieldname.'" ).val( text );
								return false;
								}
							});';
		} else {
			$tpl ='

			$( "#'.$fieldname.'" ).autocomplete({
				source: "index.php?module=ajax&action=filter&filtername='.$filter.'"
			});';
		}

		$this->app->Tpl->Add(AUTOCOMPLETE,$tpl);
		$this->app->Tpl->Set(strtoupper($fieldname).START,'<div style="font-size: 8pt;"><div class="ui-widget" style="font-size: 8pt;">');
		$this->app->Tpl->Set(strtoupper($fieldname).ENDE,'</div></div>');
  }

  function ChartDB($sql,$parsetarget,$width,$height,$limitmin=0,$limitmax=100,$gridy=5)
  {
    $result = $this->app->DB->SelectArr($sql);

    for($i=0;$i<count($result);$i++)
    {
      $lables[] = $result[$i]['legende'];
      $values[] = $result[$i]['wert'];
    }

    $values = array_reverse($values,false);
    $lables  = array_reverse($lables,false);

    $this->app->YUI->ChartAdd("#4040FF",$values);
    $this->app->YUI->Chart(TAB3,$lables,$width,$height,$limitmin,$limitmax,$gridy);

  }

  function Chart($parsetarget,$labels,$width=400,$height=200,$limitmin=0,$limitmax=100,$gridy=5)
  {
    $values = $labels;
     for($i=0;$i<count($values)-1;$i++)
     {
	$werte = $werte."'".$values[$i]."',";
     }
     $werte = $werte."'".$values[$i+1]."'";
     $this->app->Tpl->Set(LABELS,"[".$werte."]");

        $this->app->Tpl->Set(CHART_WIDTH,$width);
        $this->app->Tpl->Set(CHART_HEIGHT,$height);
      
        $this->app->Tpl->Set(LIMITMIN,$limitmin);
        $this->app->Tpl->Set(LIMITMAX,$limitmax);

        $this->app->Tpl->Set(GRIDX,count($values));
        $this->app->Tpl->Set(GRIDY,$gridy);

        $this->app->Tpl->Parse($parsetarget,"chart.tpl");
  }

  function ChartAdd($color,$values)
  {
     for($i=0;$i<count($values)-1;$i++)
     {
	$werte = $werte.$values[$i].",";
     }
     $werte = $werte.$values[$i+1];
     $this->app->Tpl->Add(CHARTS,"c.add('', '$color', [ $werte]);");
  } 


  function DateiUploadNeuVersion($parsetarget,$datei)
  {

    $speichern = $this->app->Secure->GetPOST("speichern");
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    $id = $this->app->Secure->GetGET("id");

    if($speichern !="")
    {
      $titel= $this->app->Secure->GetPOST("titel");
      $beschreibung= $this->app->Secure->GetPOST("beschreibung");
      $stichwort= $this->app->Secure->GetPOST("stichwort");

      $this->app->Tpl->Set(TITLE,$titel);
      $this->app->Tpl->Set(BESCHREIBUNG,$beschreibung);

      if($_FILES['upload']['tmp_name']=="")
      {
//        $this->app->Tpl->Set(ERROR,"<div class=\"error\">Keine Datei ausgew&auml;hlt!</div>");
        $this->app->Tpl->Set(ERROR,"<div class=\"info\">Bitte w&auml;hlen Sie eine Datei aus und laden Sie diese herauf!</div>");

      } else {
        //$fileid = $this->app->erp->CreateDatei($_FILES['upload']['name'],$titel,$beschreibung,"",$_FILES['upload']['tmp_name'],$this->app->User->GetName());
	$this->app->erp->AddDateiVersion($datei,$this->app->User->GetName(),$_FILES['upload']['name'], "Neue Version",$_FILES['upload']['tmp_name']);


        // stichwoerter hinzufuegen

	// woher objekt und parameter??? hoechsten copy von alter datei
	//$objekt = $this->app->DB->Select("SELECT objekt FROM datei_stichwoerter WHERE datei='$datei' LIMIT 1");
	//$objekt = $this->app->DB->Select("SELECT objekt FROM datei_stichwoerter WHERE datei='$datei' LIMIT 1");
        //$this->app->erp->AddDateiStichwort($fileid,$stichwort,$objekt,$parameter);
        header("Location: index.php?module=$module&action=$action&id=$id");
	exit;
      }

    }

    $this->app->Tpl->Set(STARTDISABLE,"<!--");
    $this->app->Tpl->Set(ENDEDISABLE,"-->");

    $this->app->Tpl->Parse($parsetarget,"datei_neudirekt.tpl");


  } 




  function DateiUpload($parsetarget,$objekt,$parameter)
  {

    $speichern = $this->app->Secure->GetPOST("speichern");
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    $id = $this->app->Secure->GetGET("id");

    if($speichern !="")
    {
      $titel= $this->app->Secure->GetPOST("titel");
      $beschreibung= $this->app->Secure->GetPOST("beschreibung");
      $stichwort= $this->app->Secure->GetPOST("stichwort");

      $this->app->Tpl->Set(TITLE,$titel);
      $this->app->Tpl->Set(BESCHREIBUNG,$beschreibung);

      if($_FILES['upload']['tmp_name']=="")
      {
        $this->app->Tpl->Set(ERROR,"<div class=\"error\">Keine Datei ausgew&auml;hlt!</div>");
      } else {
        $fileid = $this->app->erp->CreateDatei($_FILES['upload']['name'],$titel,$beschreibung,"",$_FILES['upload']['tmp_name'],$this->app->User->GetName());

        // stichwoerter hinzufuegen
        $this->app->erp->AddDateiStichwort($fileid,$stichwort,$objekt,$parameter);
        header("Location: index.php?module=$module&action=$action&id=$id");
      }

    }

    $this->app->Tpl->Set(SUBSUBHEADING,"Dateien");
    $table = new EasyTable(&$this->app);
    $table->Query("SELECT d.titel, s.subjekt, v.version, v.ersteller, v.bemerkung, d.id FROM datei d LEFT JOIN datei_stichwoerter s ON d.id=s.datei  
      LEFT JOIN datei_version v ON v.datei=d.id
      WHERE s.objekt='$objekt' AND s.parameter='$parameter' AND d.geloescht=0");

    $table->DisplayNew(INHALT,"<!--<a href=\"index.php?module=dateien&action=send&fid=%value%&ext=.jpg\"  rel=\"group\" class=\"zoom2\">
	<img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/vorschau.png\" border=\"0\"></a>-->
&nbsp;<a href=\"index.php?module=dateien&action=send&id=%value%\"><img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/download.png\" border=\"0\"></a>&nbsp;
	<!--<a href=\"index.php?module=dateien&action=edit&id=%value%\"><img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\" border=\"0\"></a>&nbsp;-->
	<a href=\"#\"onclick=\"if(!confirm('Wirklich löschen?')) return false; else window.location.href='index.php?module=dateien&action=delete&id=%value%';\"><img src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\" border=\"0\" ></a>
	");

 
    $this->app->Tpl->Parse(TAB1,"rahmen70.tpl");

    $this->app->Tpl->Parse(TAB2,"datei_neudirekt.tpl");


    $this->app->Tpl->Set(AKTIV_TAB1,"selected");
    $this->app->Tpl->Parse($parsetarget,"dateienuebersicht.tpl");

  } 


  function SortListAdd($parsetarget,&$ref,$menu,$sql,$sort=true)
  {
    $module = $this->app->Secure->GetGET("module");
    $id = $this->app->Secure->GetGET("id");

    $table = new EasyTable(&$this->app);
    if($sort)
      $table->Query($sql." ORDER by sort"); 
    else
      $table->Query($sql); 


    // letzte zeile anzeigen


    if($module=="lieferschein")
    {
    $table->AddRow(array(
	'<form action="" method="post">[ARTIKELSTART]<input type="text" size="30" name="artikel" id="artikel" onblur="window.setTimeout(\'selectafterblur()\',200);" >[ARTIKELENDE]',
	'<input type="text" name="projekt" id="projekt" size="10" readonly onclick="checkhere()" >',
	'<input type="text" name="nummer" id="nummer" size="7">',
	'<input type="text" size="8" name="lieferdatum" id="lieferdatum">',
	'<input type="text" name="menge" id="menge" size="5" onblur="window.setTimeout(\'selectafterblurmenge()\',200);">',
	'<input type="hidden" name="preis" id="preis" size="5" onclick="checkhere();">',
	'<input type="submit" value="buchen" name="ajaxbuchen"></form>'));
    $this->app->YUI->AutoComplete("artikel","lagerartikelnummerprojekt",1);
    } 

    else if ($module=="bestellung") {
    $table->AddRow(array(
	'<form action="" method="post">[ARTIKELSTART]<input type="text" size="30" name="artikel" id="artikel" onblur="window.setTimeout(\'selectafterblur()\',200);" >[ARTIKELENDE]',
	'<input type="text" name="projekt" id="projekt" size="10" readonly onclick="checkhere()" >',
	'<input type="text" name="nummer" id="nummer" size="7">',
	'<input type="text" size="8" name="lieferdatum" id="lieferdatum">',
	'<input type="text" name="menge" id="menge" size="5" onblur="window.setTimeout(\'selectafterblurmenge()\',200);">',
	'<input type="text" name="preis" id="preis" size="5" onclick="checkhere();">',
	'<input type="submit" value="buchen" name="ajaxbuchen"> <input type="hidden" name="bezeichnunglieferant" id="bezeichnunglieferant"><input type="hidden" name="bestellnummer" id="bestellnummer"></form>'));
/*
    $table->AddRow(array(
	'<b>Bezeichnung bei Lieferant</b>',
	'',
	'<b>Bestellnummer</b>',
	'&nbsp;',
	'&nbsp;',
	'&nbsp;',
	''));

    $table->AddRow(array(
	'<input type="text" size="30" name="bezeichnunglieferant" id="bezeichnunglieferant">',
	'',
	'<input type="text" name="bestellnummer" id="bestellnummer" size="7">',
	'',
	'',
	'',
	'</form>'));
*/

    $adresse = $this->app->DB->Select("SELECT adresse FROM bestellung WHERE id='$id' LIMIT 1");

    $this->app->YUI->AutoComplete("artikel","einkaufartikelnummerprojekt",1,"&adresse=$adresse");
    }

      else {
    $table->AddRow(array(
	'<form action="" method="post">[ARTIKELSTART]<input type="text" size="30" name="artikel" id="artikel" onblur="window.setTimeout(\'selectafterblur()\',200);" >[ARTIKELENDE]',
	'<input type="text" name="projekt" id="projekt" size="10" readonly onclick="checkhere()" >',
	'<input type="text" name="nummer" id="nummer" size="7">',
	'<input type="text" size="8" name="lieferdatum" id="lieferdatum">',
	'<input type="text" name="menge" id="menge" size="5" onblur="window.setTimeout(\'selectafterblurmenge()\',200);">',
	'<input type="text" name="preis" id="preis" size="5" onclick="checkhere();">',
	'<input type="submit" value="buchen" name="ajaxbuchen"></form>'));
    $this->app->YUI->AutoComplete("artikel","artikelnummerprojekt",1);
    }

    $table->headings[0]= 'Artikel';
    $table->headings[1]= 'Projekt';
    $table->headings[2]= 'Nummer';
    $table->headings[3]= 'Lieferung';
    $table->headings[4]= 'Menge';

    if($module=="lieferschein")
    $table->headings[5]= 'ausgeliefert';
    else
    $table->headings[5]= 'Preis';


    $table->widths[0]= '25%';
    $table->widths[1]= '10%';
    $table->widths[2]= '10%';
    $table->widths[3]= '10%';
    $table->widths[4]= '10%';
    $table->widths[5]= '10%';


    $this->app->YUI->DatePicker("lieferdatum");

    //$this->app->YUI->AutoComplete(ARTIKELAUTO,"artikel",array('name_de','warengruppe'),"nummer");

    if($module=="bestellung")
    $fillArtikel = "fillArtikelBestellung";
    elseif($module=="lieferschein")
    $fillArtikel = "fillArtikelLieferschein";
    else
    $fillArtikel = "fillArtikel";

    $this->app->Tpl->Add($parsetarget,
'<script type="text/javascript">
var Tastencode;

var status=1;

var nureinmal=0;

function selectafterblurmenge()
{
      '.$fillArtikel.'(document.getElementById("nummer").value,document.getElementById("menge").value);
}


function selectafterblur()
{
  if(nureinmal==0 || !isNaN(document.getElementById("artikel").value))
  {
      nureinmal=1;
      '.$fillArtikel.'(document.getElementById("artikel").value,document.getElementById("menge").value);
  }
}

function TasteGedrueckt (Ereignis) {
  if (!Ereignis)
    Ereignis = window.event;
  if (Ereignis.which) {
    Tastencode = Ereignis.which;
  } else if (Ereignis.keyCode) {
    Tastencode = Ereignis.keyCode;
  }
  if((Tastencode=="9" || Tastencode=="13") && !isNaN(document.getElementById("artikel").value) )
  {
    '.$fillArtikel.'(document.getElementById("artikel").value,document.getElementById("menge").value);
    //document.myform.konto.focus();
    status=1;
  }
}
document.onkeydown = TasteGedrueckt;


function updatehere()
{
      '.$fillArtikel.'(document.getElementById("artikel").value);

}

function checkhere()
{
var test = document.getElementById("artikel").value;
if(!isNaN(test.substr(0,6)))
      '.$fillArtikel.'(document.getElementById("artikel").value,document.getElementById("menge").value);

//if(!isNaN(test.substr(0,6))
//      fillArtikel(document.getElementById("artikel").value);
// wenn ersten 6 stellen nummer dann update
//if(!isNaN(document.getElementById("artikel").value))
//if(document.getElementById("artikel").value)
 //     fillArtikel(document.getElementById("artikel").value);

}

</script>

');

    //$this->app->YUI->AutoComplete(NUMMERAUTO,"artikel",array('nummer','name_de','warengruppe'),"nummer");


    foreach($menu as $key=>$value)
    {

      // im popup öffnen
      if($key=="add")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	  onclick=\"makeRequest(this);return false\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/new.png\"></a>&nbsp;";
      else if($key=="del")
	$tmp .= "<a onclick=\"if(!confirm('Wirklich löschen?')) return false; else window.location.href='index.php?module=$module&action=$value&sid=%value%&id=$id';\" href=\"#\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\"></a>&nbsp;";
      else if($key=="edit")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	class=\"popup\" title=\"Artikel &auml;ndern\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/edit.png\"></a>&nbsp;";

      // nur aktion ausloesen und liste neu anzeigen
      else
	$tmp .= "<a href=\"index.php?module=$module&action=$value&sid=%value%&id=$id\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/$key.png\"></a>&nbsp;";
    }
    $table->DisplayEditable($parsetarget, $tmp);
  }

  function SortListAddBestellung($parsetarget,&$ref,$menu,$sql,$sort=true,$adresse)
  {
    $module = $this->app->Secure->GetGET("module");
    $id = $this->app->Secure->GetGET("id");

    $table = new EasyTable(&$this->app);
    if($sort)
      $table->Query($sql." ORDER by sort"); 
    else
      $table->Query($sql); 


    // letzte zeile anzeigen
    $table->AddRow(array(
	'<form action="" method="post">[ARTIKELAUTOSTART]<input type="text" size="20" name="artikel" id="artikel" onblur="window.setTimeout(\'selectafterblur()\',200);" >[ARTIKELAUTOEND]',
	'<input type="text" name="projekt" id="projekt" size="10" readonly onclick="checkhere()">',
	'<input type="text" name="nummer" id="nummer" size="10" readonly onclick="checkhere()">',
	'<input type="text" size="10" name="lieferdatum" onclick="checkhere()">',
	'<input type="text" name="menge" id="menge" size="5" onclick="checkhere()">',
	'<input type="text" name="preis" id="preis" size="7" onclick="checkhere()">',
	'<input type="submit" value="buchen" name="ajaxbuchen"></form>'));

    $table->headings[0]= '<table width=200 cellpadding=0 cellspacing=0><tr><td>Artikel</td></tr></table>';
    $table->headings[1]= 'Projekt';
    $table->headings[2]= 'Nummer';
    $table->headings[3]= 'Lieferdatum';
    $table->headings[4]= 'Menge';
    $table->headings[5]= 'Preis';

    $this->app->YUI->AutoComplete(ARTIKELAUTO,"artikel",array('name_de','warengruppe'),"id");

    $this->app->Tpl->Add($parsetarget,
'<script type="text/javascript">
var Tastencode;

var status=1;

var nureinmal=0;

function selectafterblur()
{
  if(nureinmal==0 || !isNaN(document.getElementById("artikel").value))
  {
      nureinmal=1;
      fillArtikelBestellung(document.getElementById("artikel").value,'.$adresse.');
  }
}

function TasteGedrueckt (Ereignis) {
  if (!Ereignis)
    Ereignis = window.event;
  if (Ereignis.which) {
    Tastencode = Ereignis.which;
  } else if (Ereignis.keyCode) {
    Tastencode = Ereignis.keyCode;
  }
  if((Tastencode=="9" || Tastencode=="13") && !isNaN(document.getElementById("artikel").value) )
  {
    fillArtikelBestellung(document.getElementById("artikel").value,'.$adresse.');
    //document.myform.konto.focus();
    status=1;
  }
}
document.onkeydown = TasteGedrueckt;


function checkhere()
{
if(!isNaN(document.getElementById("artikel").value))
      fillArtikel(document.getElementById("artikel").value);

}

</script>

');

    //$this->app->YUI->AutoComplete(NUMMERAUTO,"artikel",array('nummer','name_de','warengruppe'),"nummer");


    foreach($menu as $key=>$value)
    {

      // im popup öffnen
      if($key=="add")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	  onclick=\"makeRequest(this);return false\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/new.png\"></a>&nbsp;";
      else if($key=="del")
	$tmp .= "<a onclick=\"if(!confirm('Wirklich löschen?')) return false; else window.location.href='index.php?module=$module&action=$value&sid=%value%&id=$id';\" href=\"#\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\"></a>&nbsp;";
      else if($key=="edit")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	onclick=\"makeRequest(this);return false\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/$key.png\"></a>&nbsp;";

      // nur aktion ausloesen und liste neu anzeigen
      else
	$tmp .= "<a href=\"index.php?module=$module&action=$value&sid=%value%&id=$id\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/$key.png\"></a>&nbsp;";
    }
    $table->DisplayNew($parsetarget, $tmp);
  }




  function SortList($parsetarget,&$ref,$menu,$sql,$sort=true)
  {
    $module = $this->app->Secure->GetGET("module");
    $id = $this->app->Secure->GetGET("id");

    $table = new EasyTable(&$this->app);
    if($sort)
      $table->Query($sql." ORDER by sort"); 
    else
      $table->Query($sql); 

    foreach($menu as $key=>$value)
    {

      // im popup öffnen
      if($key=="add")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	  onclick=\"makeRequest(this);return false\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/new.png\"></a>&nbsp;";
      else if($key=="del")
	$tmp .= "<a onclick=\"if(!confirm('Wirklich löschen?')) return false; else window.location.href='index.php?module=$module&action=$value&sid=%value%&id=$id';\" href=\"#\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/delete.gif\"></a>&nbsp;";
      else if($key=="edit")
	$tmp .= "<a href=\"index.php?module=$module&action=$value&id=%value%&frame=false&pid=$id\" 
	onclick=\"makeRequest(this);return false\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/$key.png\"></a>&nbsp;";

      // nur aktion ausloesen und liste neu anzeigen
      else
	$tmp .= "<a href=\"index.php?module=$module&action=$value&sid=%value%&id=$id\"><img border=\"0\" src=\"./themes/{$this->app->Conf->WFconf[defaulttheme]}/images/$key.png\"></a>&nbsp;";
    }
    $table->DisplayNew($parsetarget, $tmp);
  }

  function SortListEvent($event,$table,$fremdschluesselindex)
  {
    $sid = $this->app->Secure->GetGET("sid");
    $id = $this->app->Secure->GetGET("id");
    
    $sort = $this->app->DB->Select("SELECT sort FROM $table WHERE id='$sid' LIMIT 1");   

    if($event=="up")
    {
      //gibt es ein element an hoeherer stelle?
      $nextsort = $this->app->DB->Select("SELECT sort FROM $table WHERE $fremdschluesselindex='$id' AND sort ='".($sort+1)."' LIMIT 1");
      if($nextsort > $sort)
      {
				$nextid = $this->app->DB->Select("SELECT id FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort+1)."' LIMIT 1");
				$this->app->DB->Update("UPDATE $table SET sort='$nextsort' WHERE id='$sid' LIMIT 1");
				$this->app->DB->Update("UPDATE $table SET sort='$sort' WHERE id='$nextid' LIMIT 1");
      } else {
	// element ist bereits an oberster stelle
      }
    }
    else if($event=="down")
    {
      //gibt es ein element an hoeherer stelle?
      $prevsort = $this->app->DB->Select("SELECT sort FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort-1)."' LIMIT 1");
      if($prevsort < $sort && $prevsort!=0)
      {
				$previd = $this->app->DB->Select("SELECT id FROM $table WHERE $fremdschluesselindex='$id' AND sort = '".($sort-1)."' LIMIT 1");
				$this->app->DB->Update("UPDATE $table SET sort='$prevsort' WHERE id='$sid' LIMIT 1");
				$this->app->DB->Update("UPDATE $table SET sort='$sort' WHERE id='$previd' LIMIT 1");
      } else {
	// element ist bereits an oberster stelle
      }
    }
    else if($event=="del")
    {
			if($sid>0)
			{
        $this->app->DB->Delete("DELETE FROM $table WHERE id='$sid' LIMIT 1");
        $this->app->DB->Delete("UPDATE $table SET sort=sort-1 WHERE id='$sid' AND sort > $sort LIMIT 1");
			}
    }
    else {}

  }

  function IframeDialog($width,$height,$src="")
  {
    $id = $this->app->Secure->GetGET("id");
    $sid = $this->app->Secure->GetGET("sid");
    $module = $this->app->Secure->GetGET("module");
    $action = $this->app->Secure->GetGET("action");
    if($src!="")
      $this->app->Tpl->Set(PAGE,"<iframe width=\"$width\"  height=\"$height\" frameborder=\"0\" src=\"$src&iframe=true\"></iframe>");
    else
      $this->app->Tpl->Set(PAGE,"<iframe width=\"$width\"  height=\"$height\" frameborder=\"0\" src=\"index.php?module=$module&action=$action&id=$id&sid=$sid&iframe=true\"></iframe>");
    $this->app->BuildNavigation=false;

  }

  function Dialog($table,$parsetarget,$ueberschrift,$index_beschriftung,$formtemplate,&$object_for_function, $function_for_content,$width=320)
  {

    for($i=0; $i < count($table->datasets); $i++){
      $id = $table->datasets[$i][id]; 
      $beschriftung = $table->datasets[$i][$index_beschriftung]; 
      $js .=  '
       // Instantiate a Panel from markup
       YAHOO.example.container.panel'.$id.' = new YAHOO.widget.Panel("panel'.$id.'", { width:"'.$width.'px", visible:false, constraintoviewport:true } );
       YAHOO.example.container.panel'.$id.'.render();

       YAHOO.util.Event.addListener("show'.$id.'", "click", YAHOO.example.container.panel'.$id.'.show, YAHOO.example.container.panel'.$id.', true);
       YAHOO.util.Event.addListener("hide'.$id.'", "click", YAHOO.example.container.panel'.$id.'.hide, YAHOO.example.container.panel'.$id.', true);';

      $yui_html = '
       <div><a id="show'.$id.'">Details</a></div>
       <div id="panel'.$id.'"><div class="hd">'.$ueberschrift.' '.$beschriftung.'</div> 
       <div class="bd">[PANEL'.$id.']</div> 
       <div class="ft" align="right"><input type="submit" value="OK"></div> 
       </div>';

    if($i==0)
      $this->app->Tpl->Set(DETAILS.$id,'<div id="container">'.$yui_html);
    else if($i==count($table->datasets)-1)
      $this->app->Tpl->Set(DETAILS.$id,$yui_html."</div>");
    else
      $this->app->Tpl->Set(DETAILS.$id,$yui_html);

    // aufrufen der uebergebenen funktion
    $object_for_function->$function_for_content($id,&$this);

    // formular parsen
    $this->app->Tpl->Parse(PANEL.$id,$formtemplate);
  }
    $this->app->Tpl->Add(YUI,$js);
  }


  function AutoCompleteNeu($parsetarget,$name,$cols,$returncol,$filter="",$table="")
  {


        $colsstring = base64_encode(implode(',',$cols));
        $returncol = base64_encode($returncol);


$tpl_start = '
        <!--begin custom header content for this example-->
        <style type="text/css">
        #myAutoComplete'.$name.' {
            padding-bottom:1.3em;
        }
        .match {
            font-weight:bold;
        }
        </style>

<div id="myAutoComplete'.$name.'">';

  //<input id="myInput" type="text">

$tpl_end ='  <div id="myContainer'.$name.'"></div>
</div>

<script type="text/javascript">
YAHOO.example.BasicRemote = function() {
    // Use an XHRDataSource
    var oDS = new YAHOO.util.XHRDataSource("index.php?module=adresse&action=autocomplete&filter='.$filter.'&name='.$name.'&table='.$table.'&colsstring='.$colsstring.'&returncol='.$returncol.'&");
    // Set the responseType
    oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
    // Define the schema of the delimited results
    oDS.responseSchema = {
        recordDelim: "\n",
        fieldDelim: "\t"
    };
    // Enable caching
    oDS.maxCacheEntries = 5;


    // Instantiate the AutoComplete
    var oAC = new YAHOO.widget.AutoComplete("'.$name.'", "myContainer'.$name.'", oDS);
    oAC.queryQuestionMark =false;
    oAC.allowBrowserAutoComplete=false;
    oAC.useShadow = true;
    oAC.animHoriz = true;
    oAC.maxResultsDisplayedInput = 20;
    oAC.resultTypeList = false;
  
    oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
    var sMarkup = (sResultMatch) ? sResultMatch : "";
    var Satz =sMarkup;

    var NeuerSatz = Satz+"";

    //NeuerSatz = NeuerSatz.split(\'!*!\');
    NeuerSatz = NeuerSatz.replace("!*!"," ");

      return NeuerSatz;
    //  return sMarkup;
    };


    // when an item gets selected and populate the input field
    var myHandler = function(sType, aArgs) {
        var myAC = aArgs[0]; // reference back to the AC instance
        var elLI = aArgs[1]; // reference to the selected LI element
        var oData = aArgs[2]; // object literal of selected items result data
       
	var Satz = aArgs[2];
 
	//var Woerter = Satz.split(" ");

	var NeuerSatz = Satz+"";

	NeuerSatz = NeuerSatz.split("!*!");
        
        myAC.getInputEl().value = NeuerSatz[0];
    };
    oAC.itemSelectEvent.subscribe(myHandler);
    


   
    return {
        oDS: oDS,
        oAC: oAC
    };
}();
</script>';


    $this->app->Tpl->Add($parsetarget.START,$tpl_start);
    $this->app->Tpl->Add($parsetarget."END",$tpl_end);

  }


  function AutoCompleteAlt($parsetarget,$name,$cols,$returncol,$filter="",$table="")
  {
    if($table=="")
      $table=$name;

    if($filter=="kunde")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id WHERE adresse_rolle.subjekt='Kunde' AND adresse.kundennummer!=0 AND adresse.geloescht=0";

    if($filter=="mitarbeiter")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id WHERE adresse_rolle.subjekt='Mitarbeiter' AND adresse.mitarbeiternummer!=0 AND adresse.geloescht=0";


    if($filter=="lieferant")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id WHERE adresse_rolle.subjekt='Lieferant' AND adresse.geloescht=0";

    if($filter=="kunde_auftrag")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id LEFT JOIN auftrag ON auftrag.adresse=adresse.id WHERE adresse_rolle.subjekt='Kunde' AND ((auftrag.status='freigegeben' OR auftrag.status='storniert') OR (auftrag.vorkasse_ok=0 AND (auftrag.zahlungsweise='paypal' OR auftrag.zahlungsweise='vorkasse' OR auftrag.zahlungsweise='kreditkarte'))) AND adresse.geloescht=0";

    if($filter=="kunde_rechnung")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id LEFT JOIN rechnung ON rechnung.adresse=adresse.id WHERE adresse_rolle.subjekt='Kunde' AND rechnung.ist < rechnung.soll AND adresse.geloescht=0";

    if($filter=="kunde_gutschrift")
      $filter = "LEFT JOIN adresse_rolle ON adresse_rolle.adresse=adresse.id LEFT JOIN gutschrift ON gutschrift.adresse=adresse.id WHERE adresse_rolle.subjekt='Kunde' AND adresse.geloescht=0";

    if(($filter=="" || $filter=="adresse") && $name=="adresse")
      $filter = "WHERE adresse.geloescht=0";

    $tpl_start = '
	<!--begin custom header content for this example-->
	<style type="text/css">
	#myAutoComplete'.$name.' {
	    padding-bottom:1.3em;
	}
	.match {
	    font-weight:bold;
	}
	</style>


	<div id="myAutoComplete'.$name.'">
	    ';

      $tpl_end = '
	<div id="myContainer'.$name.'"  style="z-index:10000; width:300%;"></div>
	</div>

	<script type="text/javascript">
	YAHOO.example.FnMultipleFields = function(){
	    var myContacts'.$name.' = [
      ';


      $colsstring = implode(',',$cols);
     

      $arr = $this->app->DB->SelectArr("SELECT DISTINCT $colsstring, $returncol FROM $table $filter ORDER by 1"); 

      //echo "SELECT DISTINCT $colsstring, $returncol FROM $table $filter ORDER by 1";
      foreach($arr as $key=>$value){
	$tpl_end .= '{id:"'.$value[$returncol].'", cola:"'.$value[$cols[0]].'", colb:"'.$value[$cols[1]].'", colc:"'.$value[$cols[2]].'"},';
      }

    $tpl_end .= '
    {id:"0", cola:"", colb:"", colc:""}
    ];

   // Define a custom search function for the DataSource
    var matchNames'.$name.' = function(sQuery) {
        // Case insensitive matching
        var query = sQuery.toLowerCase(),
            contact,
            i=0,
            l=myContacts'.$name.'.length,
            matches = [];
        
        // Match against each name of each contact
        for(; i<l; i++) {
            contact = myContacts'.$name.'[i];
            if((contact.cola.toLowerCase().indexOf(query) > -1) ||
                (contact.colb.toLowerCase().indexOf(query) > -1) ||
                (contact.colc && (contact.colc.toLowerCase().indexOf(query) > -1))) {
                matches[matches.length] = contact;
            }
        }
        return matches;
    };
 
    // Use a FunctionDataSource
    var oDS = new YAHOO.util.FunctionDataSource(matchNames'.$name.');
    oDS.responseSchema = {
        fields: ["id", "cola", "colb", "colc"]
    }

    // Instantiate AutoComplete
    var oAC = new YAHOO.widget.AutoComplete("'.$name.'", "myContainer'.$name.'", oDS);
    oAC.useShadow = true;
    oAC.animHoriz = true;
    oAC.maxResultsDisplayedInput = 20;
    oAC.resultTypeList = false;
    
    
    // Custom formatter to highlight the matching letters
    oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
        var query = sQuery.toLowerCase(),
            cola = oResultData.cola,
            colb = oResultData.colb,
            colc = oResultData.colc || "", // Guard against null value
            query = sQuery.toLowerCase(),
            colaMatchIndex = cola.toLowerCase().indexOf(query),
            colbMatchIndex = colb.toLowerCase().indexOf(query),
            colcMatchIndex = colc.toLowerCase().indexOf(query),
            displaycola, displaycolb, displaycolc;

    if(colaMatchIndex > -1) {
            displaycola = highlightMatch(cola, query, colaMatchIndex);
        }
        else {
            displaycola = cola;
        }

        if(colbMatchIndex > -1) {
            displaycolb = highlightMatch(colb, query, colbMatchIndex);
        }
        else {
            displaycolb = colb;
        }

        if(colcMatchIndex > -1) {
            displaycolc = "(" + highlightMatch(colc, query, colcMatchIndex) + ")";
        }
        else {
            displaycolc = colc ? "(" + colc + ")" : "";
        }

        return displaycola + " " + displaycolb + " " + displaycolc;
        
    };
    
    // Helper function for the formatter
    var highlightMatch = function(full, snippet, matchindex) {
        return full.substring(0, matchindex) + 
                "<span class=\'match\'>" + 
                full.substr(matchindex, snippet.length) + 
                "</span>" +
                full.substring(matchindex + snippet.length);
    };

    // when an item gets selected and populate the input field
    var myHandler = function(sType, aArgs) {
        var myAC = aArgs[0]; // reference back to the AC instance
        var elLI = aArgs[1]; // reference to the selected LI element
        var oData = aArgs[2]; // object literal of selected items result data
        
        
        myAC.getInputEl().value = oData.id;
    };
    oAC.itemSelectEvent.subscribe(myHandler);
    
    return {
        oDS: oDS,
        oAC: oAC 
    };
}();
</script>';

    $this->app->Tpl->Add($parsetarget.START,$tpl_start);
    $this->app->Tpl->Add($parsetarget."END",$tpl_end);
  }

}
?>
