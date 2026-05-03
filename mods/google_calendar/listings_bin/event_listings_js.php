<?php

if(!defined("PHORUM")) return;

// print out the javascript for the event listing
print "<script type=\"text/javascript\">\n".
    "var phorum_mod_google_calendar_cookie_timeout = ".($PHORUM["session_timeout"] * 8640000).";\n".    
    file_get_contents("./mods/google_calendar/listings_bin/toggle_event_listings.js");

// only show the event bubble javascript if event data has been generated
if (!empty($PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js"])) {
    // print out the necessary variables
    print "var phorum_mod_google_calendar_event_bubble_HTML = \"".$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventBubbleHTML"]."\";\n";
    
    // print out the javascript event data for the extra events, if any
    if (!empty($PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js_extra"])) {
        print "var phorum_mod_google_calendar_js_extra_event_data = new Array();\n";
        foreach ($PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js_extra"] as $day => $extra_rows) {
            // print out the current days row data, if any
            if (!empty($extra_rows)) {
                print "phorum_mod_google_calendar_js_extra_event_data[\"".$day."MED\"] = \"";
                foreach($extra_rows as $row => $event_data) {
                    // print out the current rows event url, if it exists
                    if (!empty($event_data["read_url"])) {
                        print "<a href='".$event_data["read_url"]."'>";
                        // print out the start time for single day events
                        if (!empty($event_data["start_time"])) print $event_data["start_time"]." ";
                        print preg_replace("/\"/","\\\"",$event_data["title"])."</a><br>";
                    }
                }
                print "\";\n";
            }
        }
    }
    
    // print out the javascript event data array
    print "var phorum_mod_google_calendar_js_event_data = new Array();\n";
    foreach($PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js"] as $message_id => $event_data) {
        print "phorum_mod_google_calendar_js_event_data[$message_id] = new Array();\n";
        foreach ($event_data as $key => $data) {
            // no need to reprint the js_id
            if ($key == "js_id") continue;
            print " phorum_mod_google_calendar_js_event_data[$message_id][\"$key\"] = \"".preg_replace("/\"/","\\\"",$data)."\";\n";
        }
    }
    print file_get_contents("./mods/google_calendar/listings_bin/event_listings_bubble.js");
}
    print "\n</script>\n";

?>
