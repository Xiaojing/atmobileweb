<?
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


function day_info($time, $offset=0) {
  $time += $offset * 24 * 60 * 60;
  return array(
    "weekday"       => date('l', $time),
    "month"         => date('F', $time),
    "month_3Let"    => date('M', $time),
    "day_num"       => date('j', $time),
    "year"          => date('Y', $time),
    "month_num"     => date('m', $time),
    "day_3Let"      => date('D', $time),
    "day_num_2dig"  => date('d', $time),
    "date"          => date('Y/m/d', $time),
    "time"          => strtotime(date("Y-m-d 12:00:00", $time))
  );
}

class SearchOptions {
  private static $options = array(
    array("phrase" => "in the next 7 days",   "offset" => 7),
    array("phrase" => "in the next 15 days",  "offset" => 15),
    array("phrase" => "in the next 30 days",  "offset" => 30),
    array("phrase" => "in the past 15 days",  "offset" => -15),
    array("phrase" => "in the past 30 days",  "offset" => -30),
    array("phrase" => "this school term",     "offset" => "term"),
    array("phrase" => "this school year",     "offset" => "year")
  );

  public static function get_options($selected = 0) {
    $out_options = self::$options;
    $out_options[$selected]['selected'] = true;
    return $out_options;
  }

  public static function search_dates($option) {
    $offset = self::$options[$option]["offset"];
    $time = time();
    $day1 = day_info($time);

    if(is_int($offset)) {
      $day2 = day_info($time, $offset);
      if($offset > 0) {
        return array("start" => $day1['date'], "end" => $day2['date']);
      } else {
        return array("start" => $day2['date'], "end" => $day1['date']); 
      }
    } else {
      switch($offset) {
        case "term":
          if($day1['month_num'] < 7) {
            $end_date = "{$day1['year']}/07/01";
	  } else {
            $end_date = "{$day1['year']}/12/31";
          }
          break;

        case "year": 
          if($day1['month_num'] < 7) {
            $end_date = "{$day1['year']}/07/01";
	  } else {
            $year = $day1['year'] + 1;
            $end_date = "$year/07/01";
          }
          break;
      }    
      return array("start" => $day1['date'], "end" => $end_date); 
    }
  }
}
/*The following code was added by Xiaojing at 12:24pm on 07/08/09*/
function alleventsURL($year, $month, $day) {
  return "allevents.php";
}

function exhibitsURL($year, $month, $day) {
  return "exhibits.php";
}

// URL DEFINITIONS
function dayURL($day, $type) {
  return "day.php?time={$day['time']}&type=$type";
}

function academicURL($year, $month) {
  return "academic.php";
}

function holidaysURL($year=NULL) {
  if(!$year) {
    $year = $_REQUEST['year'];
  }
  return "holidays.php?year=$year";
}

function religiousURL($year=NULL) {
  if(!$year) {
    $year = $_REQUEST['year'];
  }
  return "holidays.php?page=religious&year=$year";
}

function categorysURL() {
  return "categorys.php";
}
/*
function categoryURL($category) {
  $id = is_array($category) ? $category['catid'] : $category->catid;
  return "category.php?id=$id";
}
*/
function categoryURL($category) {
  switch($category){
	case "academic":
	  //echo "Show academic events";
	  return "academicevents.php";
	  break;
	case "entertainment":
	  return "entertainmentevents.php";
	  break;
	case "meeting":
	  return "meetingevents.php";
	  break;
	case "news":
	  return "newsevents.php";
	  break;
	
}
}

function subCategorysURL($category) {
  $id = is_array($category) ? $category['catid'] : $category->catid;
  return "sub-categorys.php?id=$id";
}

function detailURL($event) {
  return "detail.php?id={$event->id}";
}

function timeText($event) {
  $out = substr($event->start->weekday, 0, 3) . ' ';
  $out .= substr($event->start->monthname, 0, 3) . ' ';
  $out .= (int)$event->start->day . ' ';

  $out .= MIT_Calendar::timeText($event);
  return $out;
}
  
function briefLocation($event) {
  if($loc = $event->shortloc) {
    return $loc;
  } else {
    return $event->location;
  }
}

function ucname($name) {
  $new_words = array();
  foreach(explode(' ', $name) as $word) {
    $new_word = array();
    foreach(explode('/', $word) as $sub_word) {
      $new_word[] = ucwords($sub_word);
    }
    $new_word = implode('/', $new_word);
    $new_words[] = $new_word;
  } 
  return implode(' ', $new_words);
}

class CalendarForm extends Form {

  protected $catid;
  protected $search_options;

  public function __construct($prefix, $search_options, $catid=NULL) {
    $this->prefix = $prefix;
    $this->catid = $catid;
    $this->search_options = $search_options;
  }

  public function out($total=NULL) {
    $catid = $this->catid;
    $search_options = $this->search_options;
    require "{$this->prefix}/form.html";
  }
}

?>
