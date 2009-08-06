<?
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */

require_once "../page_builder/page_header.php";
require "labstats_lib.php";

$labid = $_REQUEST['labid'];
$today = day_info(time());

require "$prefix/detail.html";



$page->output();

?>
