<?php
$upgrade_queries[] = 
    "ALTER TABLE {$PHORUM['user_table']} 
     ADD COLUMN force_password_change TINYINT(1) NOT NULL DEFAULT 0";
?>
