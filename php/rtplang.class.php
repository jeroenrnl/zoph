<?php
/* ***************************************************************************
 * Copyright (C) 2001 Eric Seigne <erics@rycks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * ***************************************************************************
 * File  : rtplang.class.php
 * Author  : Eric SEIGNE
 *           mailto:erics@rycks.com
 *           http://www.rycks.com/
 * Date    : 09/09/2001
 * Licence : GNU/GPL Version 2 ou plus
 *
 * Description:
 * ------------
 *
 *
 * 
 * @version    1.0
 * @author     Eric Seigne
 * @project    AbulEdu
 * @copyright  Eric Seigne 09/09/2001
 *
 * ************************************************************************* */

if(isset($RTPLANG_CLASS)){
  return;
}
$RTPLANG_CLASS=1;


Class rtplang {
  var $tab_langs;
  var $tab_translate;
  var $file_lang;
  /** Default language interface (isocode) */
  var $defaultiso;
  /** Source language (isocode) */
  var $sourceiso;
  /** This session language (isocode) */
  var $sessioniso;
  /** Where are languages files ? */
  var $dir;
  var $debug;

  //-------------------------------------------------
  /** Constructor */
  function rtplang($dir = "", $sourceiso = "", $defaultiso = "", $sessioniso = ""){
    $this->tab_langs = array();
    $this->tab_translate = array();
    $this->file_lang = "";
    $this->debug = 0;
    $this->dir = $dir;
    $this->sessioniso = $sessioniso;
    $this->sourceiso = $sourceiso;
    $this->defaultiso = $defaultiso;
    
    //Si on a une langue par defaut
    if(($this->defaultiso != "") && ($this->sessioniso == ""))
      $this->file_lang = $this->dir . "/" . $this->defaultiso;
    else if($this->sessioniso != "")
      $this->file_lang = $this->dir . "/" . $this->sessioniso;

    /* initialize tabs */
    $i = 0;
    if(is_dir($this->dir)) {
      $handle=opendir($this->dir);
      while ($file = trim(readdir($handle))){
	if($file != "." && $file != "..") {
	  $filet = $this->dir . "/" . $file;
	  if($fp = @fopen($filet,"r")){
	    $finded = 0;
	    while (($ligne = fgets($fp,10000)) && ($finded == 0)){
	      if($ligne[0] == "#" && $ligne[1] == "{" && $ligne[2] == "@") {
		$ligneok = "array(" . substr($ligne,2,strlen($ligne)-4) . ");";
		eval("\$tablanginfo = $ligneok;");
		$this->tablangs["htmltagoption"][$i] = $tablanginfo["htmltagoption"];
		$this->tablangs["charset"][$i] = $tablanginfo["charset"];
		$this->tablangs["name"][$i] = $tablanginfo["name"];
		$this->tablangs["iso"][$i] = $file;
		$finded = 1;
		//print "fichier indice $i $file " . $tablanginfo["charset"] . "<br>\n";
		$i++;
		
	      }
	    }
	    fclose($fp);
	  }
	}
      }
      closedir($handle);
    }
  }

  //-------------------------------------------------
  /**
   *  Return translated version of parameter string 
   *  [fr] Retourne la version traduite du texte passé en paramètre
   *       Si il n'y a pas de correspondance pour ce texte, il est retourné
   *       "tel quel" précédé d'un "<b>[vo]</b> <i>" et terminé par un </i>
   *
   *  @access     public
   *  @return     string     translated version of parameter string, or original version of this string with "<b>[vo]</b> <i>" before and "</i>" after
   *  @param      string     $str  original string to translate
   *  @param      int        $mark bolean, 1 or nothing: add [vo] if this translation does not exists, 0 don't add [vo] tags
   */
  function translate($str, $mark){
    //Si le tableau des langues n'est pas défini c'est que c'est le 1er appel
    if((count($this->tab_translate) < 1)  && (trim($this->file_lang) != "")){
      if($fp = @fopen($this->file_lang,"r")){
	while ($ligne = fgetcsv($fp,10000, "=")){
	  //On ne prends pas en compte les commentaires etc.
	  if(trim($ligne[0]) != "")
	    if($ligne[0][0] != "#" && $ligne[0][0] != ";"){
	      if(isset($ligne[1]) && $ligne[1] != "")
		$this->tab_translate[$ligne[0]] = $ligne[1];
	    }
	}
	fclose($fp);
      }
      else
	if($this->debug)
	  print "File <b>- $this->file_lang -</b> is unreadable";
    }
    $retour = $this->tab_translate[$str];
    
    if($retour == "") {
      //Si on est pas déjà en vo, on le marque
      if($this->sessioniso && $this->sourceiso != $this->sessioniso && $mark)
	$retour = "<b>[vo]</b> <i>$str</i>";
      else
	$retour = $str;
    }
    return $retour;
  }
  
  //-------------------------------------------------
  /**
   *  Return the list of available languages
   *  [fr] Retourne la liste des langues disponibles
   *
   *  @access     public
   *  @return     array: list of languages
   */
  function get_available_languages()
    {
      $tab = array();

      if($this->sessioniso != "") {
	$tab[$this->sessioniso] = array($this->sessioniso => "");
	$tab[$this->sourceiso] = array($this->sourceiso => "");
      }
      else if($this->defaultiso != "") {
	$tab[$this->defaultiso] = array($this->defaultiso => "");
	$tab[$this->sourceiso] = array($this->sourceiso => "");
      }
      else {
	$tab[$this->sourceiso] = array($this->sourceiso => "");
      }
      
      for($i = 0; $i < count($this->tablangs["iso"]); $i++) {
	$isocode = $this->tablangs["iso"][$i];
	$lang = $this->tablangs["name"][$i];
	$tab[$isocode] = array($isocode => $lang);
      }
      return $tab;
    }
  
  //-------------------------------------------------
  /**
   *  Send header and return a string of html start page
   *  [fr] Expédie le header correct et retourne le début de la page html
   *
   *  @access     public
   *  @return     string
   */
  function lang_header()
    {
      $search = "";
      $ind = 0;

      if($this->sessioniso != "")
	$search = $this->sessioniso;
      else
	$search = $this->defaultiso;

      // indice du tab ?
      for($i = 0; $i < count($this->tablangs["iso"]) && !$ind; $i++)
	if($this->tablangs["iso"][$i] == $search)
	  $ind = $i;

      $htmltag = "<html";
      if($this->tablangs["htmltagoption"][$ind] != "nothing" && $this->tablangs["htmltagoption"][$ind] != "")
	$htmltag .= " " . $this->tablangs["htmltagoption"][$ind];
      $htmltag .= ">";

      if($this->tablangs["charset"][$ind] == "")
	$charset = "iso-8859-1";
      else
	$charset = $this->tablangs["charset"][$ind];

      //      print "fichier indice $ind $search / $charset" ;
      
      header("Content-Type: text/html; charset=$charset");
      $texte .= "$htmltag
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\n";
      
      return $texte;
    }


  //-------------------------------------------------
  /**
   *  Send string number $pos
   *  [fr] Retourne le tableau avec les valeurs de la case $pos
   *
   *  @access     public
   *  @return     int: -1 on error
   *  @return     int: or array with strings
   *  @param      int        $pos position number to return
   */
  function getstring($pos) {
    $i = 0;
    $nb = count($this->tab_translate);
    if($nb == 0)
      $t = $this->translate("",0);
    $nb = count($this->tab_translate);

    reset($this->tab_translate);
    //    print "cherche indice $pos et il y a $nb strings ...<br>\n";
    if($nb > $pos) 
      while ($row = each($this->tab_translate)) {
	//	print "on est sur la position $i<br>\n";
	if($i == $pos) {
	  //	  print "trouvé " . $row[0] . ":" . $row[1] . "<br>\n";
	  return $row;
	}
	$i++;
      }
    else {
      //      print "<p>retourne -1 pour indice $pos</p>\n";
      return -1;
    }
  }

  //-------------------------------------------------
  /**
   *  Send number of available strings to translate
   *  [fr] Retourne le nombre de chaines à traduire
   *
   *  @access     public
   *  @return     int: number of strings
   */
  function getnbstrings() {
    $nb = count($this->tab_translate);
    if($nb == 0)
      $t = $this->translate("",0);
    $nb = count($this->tab_translate);
    return $nb;
  }

  //-------------------------------------------------
  /**
   *  Test if this language already exists
   *  [fr] Teste si cette langue existe déjà (traduction déjà faite)
   *
   *  @access     public
   *  @return     int: -1 on error
   *  @return     int: 0 if doest not exists, 1 if exist
   *  @param      string        $iso  isocode of this language
   *  @param      string        $name complete name of this language
   */
  function lang_exists($iso, $name) {
    $filet = $this->dir . "/" . $iso;
    //    print "appel de lang_exists ...$iso / $name ($filet)<br>";
    $retour = -1;
    if(file_exists($filet)) {
      if($fp = @fopen($filet,"r")) {
	$finded = 0;
	while (($ligne = fgets($fp,10000)) && ($finded == 0)){
	  if($ligne[0] == "#" && $ligne[1] == "{" && $ligne[2] == "@") {
	    $ligneok = "array(" . substr($ligne,2,strlen($ligne)-4) . ");";
	    eval("\$tablanginfo = $ligneok;");
	    $langname = $tablanginfo["name"];
	    $finded = 1;
	  }
	}
	fclose($fp);
      }
      if(trim(strtolower($langname)) == trim(strtolower($name)))
	$retour = 1;
      else
	$retour = 0;
    }
    else
      $retour = 0;
    //    print "retour de lang_exists *$retour*<br>";
    return $retour;
  }

  //-------------------------------------------------
  /**
   *  Save translated strings
   *  [fr] Enregistre les chaines de traduction
   *
   *  @access     public
   *  @return     0 on error
   *  @return     1 if okay
   *  @param      int        $pos int position
   *  @param      int        $tabref references array (url encoded)
   *  @param      int        $tabres translated strings array
   *  @param      int        $nbstringsperpage number of strings to translate by page
   *  @param      int        $iso isocode of this language
   *  @param      int        $langname name of this language
   *  @param      int        $translatoremail translator email
   *  @param      int        $translatorfname translator first name
   *  @param      int        $translatorlname translator last name
   *  @param      int        $end if this is last packet to save
   */
  function save($pos, $tabref, $tabres, $nbstringsperpage, $iso, $langname, $translatoremail, $translatorfname, $translatorlname, $end = 0) {
    /*
      What is this situation ?
       - first savefile call we must create this file
       - next we just add strings (append to file)

      But problems
       - append something already writed
       - 
    */
    $retour = 0;
    //    print "post = $pos<br>";
    if($end == 1) {
      // Sort and unique this tab, then save all this file !
      $retour = $this->read_sort_and_save($dlangiso, $dlangname);
    }
    else {
      if($pos-$nbstringsperpage == 0) {
	$texte = '#do not change next line please !
#{@"htmltagoption"=>"nothing","charset"=>"iso-8859-1","name"=>"' . $langname . '","translator_0"=>"' . $translatorfname . ' ' . $translatorlname . ' ' . ' <' . $translatoremail . '>"}' . "\n";
      }
      for($i = $pos-$nbstringsperpage; $i < $pos; $i++) {
	//	print "i est $i et tabref !" . $tabref[$i] . "!<br>";
	if(trim(urldecode($tabref[$i])) != "")
	  $texte .= urldecode($tabref[$i]) . "=" . $tabres[$i] . "\n";
      }
      $filet = $this->save_find_file($iso,$langname);

      if($pos-$nbstringsperpage == 0)
	$openmode = "w";
      else if($pos-$nbstringsperpage > 0)
	$openmode = "a";
      
      //      print "ouverture de $filet en ecriture($openmode) ... <br>\n";
      if($fp = @fopen($filet,$openmode)) {
	$res = fputs($fp,$texte);
	if($res)
	  $retour = 1;
	fclose($fp);
      }
      else
	$retour = sprintf("ERROR: impossible to open this file (%s) for writing !",$filet);
    }
    return $retour;
  }

  // ######################################### PRIVATE

  //-------------------------------------------------
  /**
   *  Find a filename for saving strings
   *  [fr] Trouve un nom de fichier pour enregistrer la traduction
   *
   *  @access     private
   *  @return     string     file name to use
   *  @param      int        $iso isocode of this language
   *  @param      int        $lang name of this language
   */
  function save_find_file($iso, $lang) {
    $file = $this->dir . "/" . $iso;
    // This isocode file exists but with another language name ... make $iso$num file
    // but problem is to remember this isonum .. that's okay, but a good comment is
    // welcome for this obscur code !
    //    print "test de fichier $file<br>\n";
    if(!file_exists($file)) {
      $filet = $file;
    }
    else if($this->lang_exists($iso,$lang))
      $filet = $file;
    else {
      // Search if this file does not already exists
      for($i = 0, $isot = $iso, $finded = 0; $finded == 0 && file_exists($this->dir . "/" . $isot); $i++, $isot="$iso$i")
	if($this->lang_exists($isot,$lang)) {
	  $filet = "$file$i";
	  $finded = $i;
	}
      if($finded == 0) {
	for($i = 0; file_exists($filet); $i++, $filet="$file$i");
      }
    }
    
    //    print "retourne fichier * $filet *<br>\n";
    return $filet;
  }


  //-------------------------------------------------
  /**
   *  Read file, sort array, then save cleaned file
   *  [fr] Lis le fichier, trie les chaines de traduction et enregister le résultat
   *
   *  @access     private
   *  @return     int        0 if error, 1 if ok
   *  @param      int        $iso isocode of this language
   *  @param      int        $langname name of this language
   */
  function read_sort_and_save($iso, $langname) {
    $retour = 0;
    $filet = $this->save_find_file($iso,$langname);

    // Read language heading file
    if($fp = @fopen($filet,"r")){
      $stop = 0;
      while (($ligne = fgets($fp,10000)) && ($stop == 0)){
	if($ligne[0] != "#" && $ligne[0] != ";"){
	  $stop = 1;
	}
	else
	  $texte .= $ligne;
      }
      fclose($fp);
    }

    if($fp = @fopen($filet,"r")){
      while ($ligne = fgetcsv($fp,10000, "=")){
	if(trim($ligne[0]) != "")
	  if($ligne[0][0] != "#" && $ligne[0][0] != ";"){
	    $tab[$ligne[0]] = $ligne[1];
	  }
      }
      fclose($fp);
    }
    ksort($tab);
    while ($row = each($tab))
      $texte .= $row[0] . "=" . $row[1] . "\n";
    
    if($fp = @fopen($filet,"w")) {
      $res = fputs($fp,$texte);
      if($res)
	$retour = 1;
      fclose($fp);
    }
    return $retour;
  }

}

//-------------------------------------------------
/**
 *  Return translated version of parameter string 
 *  [fr] Retourne la version traduite du texte passé en paramètre
 *       Si il n'y a pas de correspondance pour ce texte, il est retourné
 *       "tel quel" précédé d'un "<b>[vo]</b> <i>" et terminé par un </i>
 *
 *  @access     public
 *  @return     string     translated version of parameter string, or original version of this string with "<b>[vo]</b> <i>" before and "</i>" after
 *  @param      string     $str  original string to translate
 *  @param      int        $mark bolean, 1 or nothing: add [vo] if this translation does not exists, 0 don't add [vo] tags
 */
function translate($str, $mark = 1){
  global $rtplang;
  return $rtplang->translate($str, $mark);
}

?>
