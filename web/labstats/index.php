<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


require_once "../page_builder/page_header.php";

require "labstats_lib.php";

class Categorys {
  public static $info = array(
	"10"           => array("Coleman/Morse Center 107", "CLMR107", "Buildings by Number"),
	"11"           => array("COBA004", "COBA004", "Buildings by Number"),
    "4"            => array("Debartolo 133", "DBRT133", "Buildings by Number"),
    "7"            => array("LAFO 016", "LAFO016", "Buildings by Names"),
    "22"           => array("Library 100", "LIBR100", "Residences"),
  );
}

$category_info = Categorys::$info;
$index = ($prefix == "ip") ? 0 : 1;

$categorys = array();
foreach($category_info as $category => $title) {
  $categorys[$category] = $title[$index];
}
$today = day_info(time());
require "$prefix/index.html";

$page->output();

function places() {
  require "buildings.php";

  if($_REQUEST['category'] == 'buildings') {
    // array needs to be converted to a hash
    $places = array();
    foreach($buildings as $building_number) {
      $places[$building_number] = $building_number;
    }
  } else {
    $places = ${$_REQUEST['category']};
  }
  return $places;
}

function places_sublist($listName) {
  if($_REQUEST['category'] == 'buildings') {
    $drill = new DrillNumeralAlpha($listName, "key");
  } else {
    $drill = new DrillAlphabeta($listName, "key");
  }
  return $drill->get_list(places());
}


function drillURL($drilldown, $name=NULL) {
  $url = categoryURL() . "&drilldown=$drilldown";
  if($name) {
    $url .= "&desc=" . urlencode($name);
  }
  return $url;
}

function categoryURL($category=NULL) {
  $category = $category ? $category : $_REQUEST['category'];
  return "?category=$category";
}




function labstatsURL($category=NULL) {
 
  return "detail.php?labid=$category";
}
    
?>
