<?php

if(!defined("PHORUM_ADMIN")) return;

$month_options = "";
for ($i=1;$i<=12;$i++) {
    $month_options .= "<option value=\"$i\">$i</option>";
}
$day_options = "";
for ($i=1;$i<=31;$i++) {
    $day_options .= "<option value=\"$i\">$i</option>";
}
$hour_options = "<option value=\"0\">12am</option>";
for ($i=1;$i<=11;$i++) {
    $hour_options .= "<option value=\"$i\">{$i}am</option>";
}
$hour_options .= "<option value=\"12\">12pm</option>";
for ($i=13;$i<=22;$i++) {
    $hour_options .= "<option value=\"$i\">".($i-12)."pm</option>";
}
$hour_options .= "<option value=\"23\">11pm</option>";
$minute_options = "";
for ($i=0;$i<=59;$i++) {
    $minute_options .= "<option value=\"$i\">".sprintf("%02d",$i)."</option>";
}
$div_fields["start_month"] = "<select id=\"google_calendar_start_month_id\" name=\"google_calendar_start_month\" onchange=\"google_calendar_check_day_value_by_month(this)\">$month_options</select>";

$div_fields["start_day"] = "<select id=\"google_calendar_start_day_id\" name=\"google_calendar_start_day\" onchange=\"google_calendar_check_day_value(this)\">$day_options</select>";

$div_fields["start_hour"] = "<select name=\"google_calendar_start_hour\">".str_replace("value=\"0\"", "value=\"0\" selected=\"selected\"", $hour_options)."</select>";

$div_fields["start_minute"] = "<select name=\"google_calendar_start_minute\">".str_replace("value=\"0\"", "value=\"0\" selected=\"selected\"", $minute_options)."</select>";

$div_fields["end_month"] = "<select id=\"google_calendar_end_month_id\" name=\"google_calendar_end_month\" onchange=\"google_calendar_check_day_value_by_month(this)\">$month_options</select>";

$div_fields["end_day"] = "<select id=\"google_calendar_end_day_id\" name=\"google_calendar_end_day\" onchange=\"google_calendar_check_day_value(this)\">$day_options</select>";

$div_fields["end_hour"] = "<select name=\"google_calendar_end_hour\">".str_replace("value=\"23\"", "value=\"23\" selected=\"selected\"", $hour_options)."</select>";

$div_fields["end_minute"] = "<select name=\"google_calendar_end_minute\">".str_replace("value=\"59\"", "value=\"59\" selected=\"selected\"", $minute_options)."</select>";

$PHORUM["phorum_mod_google_calendar"]["event_div_fields"] = $div_fields;

?>
