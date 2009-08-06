<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


require "page_builder/Page.php";

Page::classify_phone();

if(Page::is_computer() || Page::is_spider()) {
  header("Location: ./about/");
} else {
  header("Location: ./home/");
}
?>
