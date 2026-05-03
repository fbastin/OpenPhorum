<?php
// add border color for Emerald based templates
if (!empty($PHORUM["DATA"]["border_color"])) {
    $border_color = $PHORUM["DATA"]["border_color"];
// or add border color for Classic based templates
} elseif (!empty($PHORUM["DATA"]["tablebordercolor"])) {
    $border_color = $PHORUM["DATA"]["tablebordercolor"];
}
$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"] = array (
    "category_selection" => "Sélectionner une catégorie pour cet événement:",
    "create_event" => "Créer un événement pour ce sujet.",
    "EventDescription" => "Auteur: %author%<br/><a target=\"_self\" href=\"%read_url%\">Voir sujet</a>",
    "EventTitle" => "%subject%",
    "date_format" => "d,m,y", //comma separated list of m, d, and y, in the order of your choice (without spaces)
    "date_separator" => "/",
    "time_separator" => ":",
    "when_end" => "Fin de l'événement:",
    "when_start" => "Début de l'événement:",
    "where" => "Lieu de l'événement (optionnel):",
    "where_help" => "(p.e. \"Au stand\", \"Sur ce site\", etc.)", //be sure to escape quotation marks (e.g. " should be \")
    "show_start" => "Événement: ",
    "show_start_with_category" => "événement: ", //e.g. "Summer calendar event:" or "Winter calendar event:"
    "show_end" => " to ",
    "CalendarTitle" => "Calendrier des événements",
    "CalendarDescription" => "",
    "TimeFormat" => "%I:%M%p", //see http://php.net/strftime for more info on possible variables
    "WeeklyEventListing" => "Agenda des événements de cette semaine",
    "NextSevenEventListing" => "Agenda des événements pour les sept prochains jours",
    "HideEventListing" => "Masquer l'agenda",
    "ShowEventListing" => "Montrer l'agenda",
    "ViewCalendar" => "Voir le calendrier complet",
    "MoreEvents" => "+ %count%",
    "event_listing_date_format" => "d/m",  //see http://php.net/date for more info on possible variables
    "Days" => array ( //you can empty this array to only show the date as the header for the event listing columns
        "Dim",
        "Lun",
        "Mar",
        "Mer",
        "Jeu",
        "Ven",
        "Sam",
        "Dim",
        ),
    "EventBubbleWhere" => "Lieu: %where%<br/>", //you should always include the %where% variable somewhere in this field
    
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
