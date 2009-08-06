<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


require "../../lib/ShuttleSchedule.php";
require "../page_builder/page_header.php";
require "schedule_lib.php";

$schedule = new ShuttleSchedule();

//include all the shuttle schedule data
require "../../lib/shuttle_schedule.php";
//The following original code was commented by Xiaojing at 10:39am on 06/19/09 to avoid the fatal error throwed in requrest xml from http://www.nextbus.com/s/xmlFeed?command=predictionsForMultiStops&a=mit
//$schedule->initRoutesCache();

$now = time();
$day = date('D', $now);
$hour = date('H', $now);
$minute = date('i', $now);

$day_keys = array(
  "tech_shuttle",
  "northwest_shuttle",
  "boston_daytime"
);

$night_keys = array(
  "boston_east",
  "boston_west",
  "cambridge_east",
  "cambridge_west"
);
  
$day_routes = array();
foreach($day_keys as $key) {
  $day_routes[$key] = $schedule->getRoute($key);
}

$night_routes = array();
foreach($night_keys as $key) {
  $night_routes[$key] = $schedule->getRoute($key);
}

$routes = array_merge($day_routes, $night_routes);

require "$prefix/index.html";
$page->output();
?>
