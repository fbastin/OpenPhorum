<?php
if (!defined("PHORUM")) return;

$sqlqueries[]= "
  CREATE TABLE {$GLOBALS['PHORUM']['single_notify_table']} (
	 `email` VARCHAR( 255 ) NOT NULL ,
	`forum_id` INT UNSIGNED NOT NULL ,
	`thread_id` INT UNSIGNED NOT NULL ,
	PRIMARY KEY ( `email` , `forum_id` , `thread_id` ) 
  )
";

?>
