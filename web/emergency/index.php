<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */

/** MIT Emergency Contact No.
$main = array(
  i("6172531212", "Campus Police"),
  i("6172531311", "MIT Medical"),
  i("6172537669", "Emergency Status")
);
$others = array(
  i("6172531311", "MIT Medical", "24-hour urgent care"),
  i("617253SNOW", "Emergency Closings", "recorded updates"),
  i("6173245031", "International SOS", "emergency medical and security evacuation services for those traveling abroad on MIT business"),
  i("6172534948", "Facilities", "24-hour emergency repairs"),
  i("6172538800", "Nightline", "MIT student hotline 7pm-7am"),
  i("6172532997", "Safe Ride", "campus transportation 6pm-3am"),
  i("6172532700", "MIT News Office"),
  i("6172534795", "Information Center"),
  i("6172531212", "MIT Police"),
  i("6172537276", "MIT Police - Guest Parking"),
  i("6172539753", "MIT Police - Lost and Found"),
  i("617253DOWN", "Computer and Communications Outages"),
  i("6174523477", "Environment, Health & Safety"),
  i("6172587366", "Security and Emergency Management Office"),
  i("6172538000", "Message Center - Fax Service"),
  i("6172533692", "Message Center - Emergency Number"),
  i("6172531000", "Telephone Service - MIT Directory Assistance"),
  i("6172534357", "Telephone Service - Service Problems"),  
  i("6172536311", "Travel directions to MIT"),
  i("6172539200", "Bates Linear Accelerator Center"),
  i("7819815555", "Haystack Observatory"),
  i("7819813333", "Lincoln Laboratory Emergencies", "security desk"),
);
**/
$main = array(
  i("5746315555", "ND Security & Police"),
  i("5746317497", "ND Health Services"),
  i("5746342583", "SAFEWALK")
);

$others = array(
  i("5746317497", "ND Health Services", "appointments"),
  i("5746316574", "ND Health Services", "prescription refills 24 hours/day"),
  i("5746316200", "ND Fire Department"),
  i("5746318338", "NDSP Administrative"),
  i("5746317367", "ND News and Information"),
  i("5746315037", "ND Risk Management and Safety"),
  i("5746313921", "ND Utilities"),
  i("5746317701", "ND Facilities Operations"),
  i("5746315900", "ND Human Resources"),
  i("5746313903", "ND President's Office"),
  i("5746316631", "ND Provost Office"),
  i("5746315053", "ND Parking Services"),
  i("5746318000", "ND Traffic/Parking Hotline"),
  i("5746315603", "ND Telephone Services"),
);

require_once "../../lib/rss_services.php";

$emergency_message = "Coming Soon: Emergency Updates"; 
$Emergency = new Emergency();
$emergency = $Emergency->get_feed();

if($emergency === False) {
  $paragraphs = array('Emergency information is currently not available');
} else {
  $text = explode("\n", $emergency['Emergency Information']['text']);
  $paragraphs = array();
  foreach($text as $paragraph) {
    if($paragraph) {
      $paragraphs[] = htmlentities($paragraph);
    }
  }
}

// the logic to implement the page begins here
require "../page_builder/page_header.php";


if(isset($_REQUEST['contacts'])) {
  require "$prefix/contacts.html";
} else {
  require "$prefix/index.html";
}

$page->output();

function contactsURL() {
  return "./?contacts=true";
}

class EmergencyItem {
  private $number;
  private $label;
  private $message;

  // letters on a phone key-pad
  private static $letters = array(
    "A-C" => 2,
    "D-F" => 3, 
    "G-I" => 4,
    "J-K" => 5,
    "M-O" => 6,
    "P-S" => 7,
    "T-V" => 8,
    "W-Z" => 9
  );

  public function __construct($number, $label, $message) {
    $this->number = $number;
    $this->label = $label;
    $this->message = $message;
  }
  
  public function call_number() {
    $init = $this->number;
    foreach(self::$letters as $letters => $digit) {
      $init = preg_replace("/[$letters]/", $digit, $init);
    }
    return $init;
  }

  public function number_text() {
    return substr($this->number, 0, 3) . "." . substr($this->number, 3, 3) . "." . substr($this->number, 6, 4);
  }

  public function label() {
    return htmlentities($this->label);
  }

  public function message_text() {
    if($this->message) {
      return htmlentities($this->message . ": ");
    } else {
      return "";
    }
  }
}

function i($number, $label, $message=NULL) {
  return new EmergencyItem($number, $label, $message);
}



?>
