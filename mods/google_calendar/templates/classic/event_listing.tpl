{IF phorum_mod_google_calendar->calendars_to_show}
    {! --- Set the styles for the event listing --- }
    <style>
    #phorum table.list td.google_calendar_td_header {
        padding: 2px;
        font-weight: bold;
        text-align: center;
        background-color: {backcolor};
        border-bottom: 1px solid #ccc;
        }
    #phorum table.list td.google_calendar_td {
        border-top: 0px;
        padding: 1px 2px 1px 2px;
        background-color: {backcolor};
        }
    .google_calendar_event {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        font-size: {smallfontsize};
        color: White;
        padding: 1px;
        cursor: pointer;
        }
    .google_calendar_more_events {
        text-decoration: underline;
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        font-size: {smallfontsize};
        padding: 1px;
        cursor: pointer;
        }
    .google_calendar_bubble {
        display: none;
        position: absolute;
        background-color: {backcolor};
        padding: 5px;
        border: 1px solid {tablebordercolor};
        }
    </style>
    {! --- This div is shown if the user has hidden the event listing --- }
    <div id="phorum_mod_google_calendar_event_listing_hidden_div" style="padding: 0px; margin: 0px; display: {phorum_mod_google_calendar->toggle_event_listing_div};">
    <table cellspacing="0" class="list" style="margin-bottom:20px; border-bottom: 1px solid #ccc;" width="100%">
        <tr>
            <th align="left" colspan="7">
                <div style="float: left;"><span style="cursor: pointer;" onclick="phorum_mod_google_calendar_show_event_listing()" title="{LANG->phorum_mod_google_calendar->ShowEventListing}">[+]</span>
                     {LANG->phorum_mod_google_calendar->EventListing}</div>
                {! --- Show a link to the embedded calendar if enabled --- }
                {IF phorum_mod_google_calendar->show_embedded_calendar}
                <div style="text-align: right; float: right;"><a href="{URL->GOOGLE_CALENDAR}">{LANG->phorum_mod_google_calendar->ViewCalendar}</a></div>
                {/IF}
            </th>
        </tr>
    </table></div>
    {! --- This div contains the actual event listing --- }
    <div id="phorum_mod_google_calendar_event_listing_shown_div" style="padding: 0px; margin: 0px; display: {phorum_mod_google_calendar->toggle_event_listing_div_opposite};">
    <table cellspacing="0" class="list" style="margin-bottom:20px; table-layout: fixed; border-bottom: 1px solid #ccc;" width="100%">
        <tr>
            <th align="left" colspan="7" style="border-bottom: 1px solid #ccc;">
                <div style="float: left;"><span style="cursor: pointer;" onclick="phorum_mod_google_calendar_hide_event_listing()" title="{LANG->phorum_mod_google_calendar->HideEventListing}">[-]</span>
                     {LANG->phorum_mod_google_calendar->EventListing}</div>
                {! --- Show a link to the embedded calendar if enabled --- }
                {IF phorum_mod_google_calendar->show_embedded_calendar}
                <div style="text-align: right; float: right;"><a href="{URL->GOOGLE_CALENDAR}">{LANG->phorum_mod_google_calendar->ViewCalendar}</a></div>
                {/IF}
            </th>
        </tr>
        <tr>
            {VAR ALT_EVENT_TD false}
            {! --- Loop through the day headers, this changes depending on if a weekly or a seven day view is chosen --- }
            {LOOP phorum_mod_google_calendar->event_listing_header}
                <th class="google_calendar_td_header" style="text-align: center;{IF ALT_EVENT_TD} background-color:{altbackcolor};{/IF}">
                    {phorum_mod_google_calendar->event_listing_header}
                </th>
                {! --- Toggle the alternating background color --- }
                {IF ALT_EVENT_TD}{VAR ALT_EVENT_TD false}{ELSE}{VAR ALT_EVENT_TD true}{/IF}
            {/LOOP phorum_mod_google_calendar->event_listing_header}
        </tr>
        
        {VAR phorum_mod_google_calendar->curr_row 0}
        {! --- Loop through the rows for each day of event listings --- }
        {LOOP phorum_mod_google_calendar->row_count}
            {VAR phorum_mod_google_calendar->curr_day 0}
            {VAR ALT_EVENT_TD false}
            {! --- Loop through the seven days of event listings --- }
            {LOOP phorum_mod_google_calendar->event_listings}
                {IF phorum_mod_google_calendar->curr_day 0}<tr>{/IF}
                <?php
                    // set the template data for the current row
                    $PHORUM["DATA"]["phorum_mod_google_calendar"]["curr_row_data"] 
                        = $PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings"][$PHORUM["DATA"]["phorum_mod_google_calendar"]["curr_day"]]["rows"][$PHORUM["DATA"]["phorum_mod_google_calendar"]["curr_row"]];
                ?>
                {! --- If this is not a place holder for a multi-day event, create a table cell --- }
                {IF NOT phorum_mod_google_calendar->curr_row_data->full_day_event}
                    <td valign="top"
                        {! --- If this is not an empty table cell add the javascript and id info for the event bubble --- }
                        {IF NOT phorum_mod_google_calendar->curr_row_data->no_event}
                            onmouseover="phorum_mod_google_calendar_cancel_closetimer('{phorum_mod_google_calendar->curr_row_data->js_id}')" onmouseout="phorum_mod_google_calendar_set_closetimer('{phorum_mod_google_calendar->curr_row_data->js_id}')"
                            id="{phorum_mod_google_calendar->curr_row_data->js_id}"
                            onclick="phorum_mod_google_calendar_show_event_bubble(this);"
                        {/IF}
                        class="google_calendar_td"
                        {! --- If we are on an alternating day, show the alternate background color --- }
                        {IF ALT_EVENT_TD}style="background-color: {altbackcolor};"{/IF}
                        {! --- If this is a multi-day event, set the number of days it spans --- }
                        {IF phorum_mod_google_calendar->curr_row_data->colspan}colspan="{phorum_mod_google_calendar->curr_row_data->colspan}"{/IF}>
                    {! --- If this is there are extra events to show, link to them in this cell --- }
                    {IF phorum_mod_google_calendar->curr_row_data->extra_rows}
                        <div class="google_calendar_more_events">
                            {phorum_mod_google_calendar->curr_row_data->MoreEvents}
                        </div>
                    {! --- If this is simply an empty cell, hold it open but without any data --- }
                    {ELSEIF phorum_mod_google_calendar->curr_row_data->no_event}&nbsp;
                    {! --- Otherwise fill in the relevant event data --- }
                    {ELSE}
                        <div 
                        class="google_calendar_event"
                        style="background-color: {phorum_mod_google_calendar->curr_row_data->color};"
                        >
                        {IF phorum_mod_google_calendar->curr_row_data->start_time}<span style="font-size: {font_x_small}">{phorum_mod_google_calendar->curr_row_data->start_time}</span> {/IF}{phorum_mod_google_calendar->curr_row_data->title}
                        </div>
                    {/IF}
                    </td>
                {/IF}
                {IF phorum_mod_google_calendar->curr_day 6}</tr>{/IF}
                <?php $PHORUM["DATA"]["phorum_mod_google_calendar"]["curr_day"] ++; ?>
                {! --- Toggle the alternating background color --- }
                {IF ALT_EVENT_TD}{VAR ALT_EVENT_TD false}{ELSE}{VAR ALT_EVENT_TD true}{/IF}
            {/LOOP phorum_mod_google_calendar->event_listings}
            <?php $PHORUM["DATA"]["phorum_mod_google_calendar"]["curr_row"] ++; ?>
        {/LOOP phorum_mod_google_calendar->row_count}
    </table>
    </div>
    {! --- Set up an empty div for the event bubble --- }
    <div class="google_calendar_bubble" id="phorum_mod_google_calendar_event_bubble_div" onmouseover="phorum_mod_google_calendar_cancel_closetimer()" onmouseout="phorum_mod_google_calendar_set_closetimer()">Empty</div>
{/IF}
