<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


require "../page_builder/page_header.php";
require "calendar_lib.php";

// this defines the text that will appear in the academica
// calendar
require "../../lib/holiday_data.php";

$year = (int) $_REQUEST['year'];
$next = $year + 1;
$prev = $year - 1;

/* The following original code was commented by Xiaojing on 11:34am 07/09/09 to not display the date information.
$holidays = $holiday_data[$year];
if($religious_days[$year]) {
  $religious = $religious_days[$year];
} else {
  $religious = array();
}


if($_REQUEST['page'] == 'religious') {
  require "$prefix/religious_text.html";
  $has_next = isset($religious_days[$next]);
  $has_prev = isset($religious_days[$prev]);
} else {
  $has_next = isset($holiday_data[$next]);
  $has_prev = isset($holiday_data[$prev]);
  require "$prefix/holidays.html";
}
*/
require "$prefix/academicevents.html";
$page->output();

function weekday($day, $year) {
  return date('D', strtotime("$day, $year"));
}

?>
