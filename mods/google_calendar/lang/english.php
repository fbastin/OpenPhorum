<?php
// add border color for Emerald based templates
if (!empty($PHORUM["DATA"]["border_color"])) {
    $border_color = $PHORUM["DATA"]["border_color"];
// or add border color for Classic based templates
} elseif (!empty($PHORUM["DATA"]["tablebordercolor"])) {
    $border_color = $PHORUM["DATA"]["tablebordercolor"];
}
$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"] = array (
    "category_selection" => "Select a category for this event:",
    "create_event" => "Create a calendar event for this topic.",
    "EventDescription" => "Author: %author%<br/><a target=\"_self\" href=\"%read_url%\">View Topic</a>",
    "EventTitle" => "%subject%",
    "date_format" => "m,d,y", //comma separated list of m, d, and y, in the order of your choice (without spaces)
    "date_separator" => "/",
    "time_separator" => ":",
    "when_end" => "When does the event end:",
    "when_start" => "When does the event start:",
    "where" => "Where will the event take place (optional):",
    "where_help" => "(e.g. \"At work\", \"On this website\", etc.)", //be sure to escape quotation marks (e.g. " should be \")
    "show_start" => "Calendar event: ",
    "show_start_with_category" => "calendar event: ", //e.g. "Summer calendar event:" or "Winter calendar event:"
    "show_end" => " to ",
    "CalendarTitle" => "Event Calendar",
    "CalendarDescription" => "",
    "TimeFormat" => "%I:%M%p", //see http://php.net/strftime for more info on possible variables
    "WeeklyEventListing" => "Calendar Events for This Week",
    "NextSevenEventListing" => "Calendar Events for the Next Seven Days",
    "HideEventListing" => "Hide Calendar Events",
    "ShowEventListing" => "Show Calendar Events",
    "ViewCalendar" => "View Full Calendar",
    "MoreEvents" => "+ %count% more",
    "event_listing_date_format" => "m/d",  //see http://php.net/date for more info on possible variables
    "Days" => array ( //you can empty this array to only show the date as the header for the event listing columns
        "Sun",
        "Mon",
        "Tue",
        "Wed",
        "Thu",
        "Fri",
        "Sat",
        "Sun",
        ),
    "EventBubbleWhere" => "Where: %where%<br/>", //you should always include the %where% variable somewhere in this field
    
    /*  The event bubble HTML is parsed to show the chosen event fields.
        The available fields are:
        %title%     - The title of the event (defaults to the subject of the 
                      post)
        %start%     - The full start date/time as determined by the 
                      short_date_time first or the long_date_time as set in your 
                      main language file
        %end%       - The full end date/time, same format as above
        %read_url%  - The link to the post for the selected event
        %where%     - The full "where" field set above
        %start_time%- The starting time for the event as determined by the 
                      TimeFormat field set above
        %color%     - The color for the selected event's category/forum/folder
    */
    "EventBubbleHTML" => "<b>%title%</b><br/>When: %start% - %end%<br/>%where%<div style=\\\"text-align: center; margin-left: -1px; margin-top: 2px; padding-top: 2px; border-top: 1px solid ".$border_color.";\\\"><a href=\\\"%read_url%\\\">View Topic</a></div>",
);
?>
