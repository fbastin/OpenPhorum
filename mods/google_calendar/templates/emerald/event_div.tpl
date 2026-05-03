<div class="generic" style="text-align: left; margin-bottom: 5px;">
    <input type="checkbox" id="google_calendar_add_event_id" name="google_calendar_add_event" {IF google_calendar_post_temp->google_calendar_add_event}{google_calendar_post_temp->google_calendar_add_event}{/IF} onclick="google_calendar_addremove_event(this)"/><span onclick="toggle_google_calendar_add_event()" style="cursor: pointer;"> {LANG->phorum_mod_google_calendar->create_event}</span>
    <div style="{IF NOT google_calendar_post_temp->google_calendar_add_event}display:none;{/IF} background-color:{default_background_color}; padding: 5px; margin-top: 3px; border: 1px solid {border_color};" id="google_calendar_event_div_id">
    <div style="padding: 5px;">{LANG->phorum_mod_google_calendar->when_start} 
    {phorum_mod_google_calendar_event_div_fields->start_date} - 
    {phorum_mod_google_calendar_event_div_fields->start_hour} {LANG->phorum_mod_google_calendar->time_separator} {phorum_mod_google_calendar_event_div_fields->start_minute}
    </div>
    <div style="padding: 5px;">{LANG->phorum_mod_google_calendar->when_end} 
    {phorum_mod_google_calendar_event_div_fields->end_date} - 
    {phorum_mod_google_calendar_event_div_fields->end_hour} {LANG->phorum_mod_google_calendar->time_separator} {phorum_mod_google_calendar_event_div_fields->end_minute}</div>
    <div style="padding: 5px;">{LANG->phorum_mod_google_calendar->where} 
    <input type="text" name="google_calendar_where" size="50" value="{IF google_calendar_post_temp->google_calendar_where}{google_calendar_post_temp->google_calendar_where}{/IF}" /> <span style="font-size:small;">{LANG->phorum_mod_google_calendar->where_help}</span></div>
    {IF phorum_mod_google_calendar_event_div_fields->category}{phorum_mod_google_calendar_event_div_fields->category}{/IF}
    </div>
</div>

<script type="text/javascript">
//toggle the add event field
function toggle_google_calendar_add_event() {
    add_event_field = document.getElementById("google_calendar_add_event_id");
    if (add_event_field.checked === true) {
        add_event_field.checked = false;
    } else {
        add_event_field.checked = true;
    }
    google_calendar_addremove_event(add_event_field);
}
//toggle the calendar event div
function google_calendar_addremove_event(curr_el) {
    google_calendar_event_div = document.getElementById("google_calendar_event_div_id");
    if (curr_el.checked === true) {
        google_calendar_event_div.style.display="block";
    } else {
        google_calendar_event_div.style.display="none";
    }
}
function google_calendar_check_day_value(curr_el) {
    if (curr_el.name.match("start")) {
        month_el = document.getElementById("google_calendar_start_month_id");
        year_el = document.getElementById("google_calendar_start_year_id");
    } else {
        month_el = document.getElementById("google_calendar_end_month_id");
        year_el = document.getElementById("google_calendar_end_year_id");
    }
    curr_year = parseInt(year_el.options[year_el.selectedIndex].value);
    if ((curr_year % 4 == 0 && curr_year % 100 != 0) || curr_year % 400 == 0) {
        maxfeb = 28;
    } else {
        maxfeb = 27;
    }
    
    if (month_el.selectedIndex == 1 && curr_el.selectedIndex > maxfeb) {
        curr_el.selectedIndex = maxfeb;
        return;
    }
    thirty_months = Array(3,5,8,10);
    for (i in thirty_months) {
        if (month_el.selectedIndex == thirty_months[i] && curr_el.selectedIndex == 30) {
            curr_el.selectedIndex = 29;
            return;
        }
    }
}
function google_calendar_check_day_value_by_month(curr_el) {
    if (curr_el.name.match("start")) {
        day_el = document.getElementById("google_calendar_start_day_id");
        year_el = document.getElementById("google_calendar_start_year_id");
    } else {
        day_el = document.getElementById("google_calendar_end_day_id");
        year_el = document.getElementById("google_calendar_end_year_id");
    }
    
    curr_year = parseInt(year_el.options[year_el.selectedIndex].value);
    if ((curr_year % 4 == 0 && curr_year % 100 != 0) || curr_year % 400 == 0) {
        maxfeb = 28;
    } else {
        maxfeb = 27;
    }
    
    if (curr_el.selectedIndex == 1 && day_el.selectedIndex > maxfeb) {
        day_el.selectedIndex = maxfeb;
        return;
    }
    thirty_months = Array(3,5,8,10);
    for (i in thirty_months) {
        if (curr_el.selectedIndex == thirty_months[i] && day_el.selectedIndex == 30) {
            day_el.selectedIndex = 29;
            return;
        }
    }
}
function google_calendar_check_day_value_by_year(curr_el) {
    if (curr_el.name.match("start")) {
        month_el = document.getElementById("google_calendar_start_month_id");
        day_el = document.getElementById("google_calendar_start_day_id");
    } else {
        month_el = document.getElementById("google_calendar_end_month_id");
        day_el = document.getElementById("google_calendar_end_day_id");
    }
    curr_year = parseInt(curr_el.options[curr_el.selectedIndex].value);
    if ((curr_year % 4 == 0 && curr_year % 100 != 0) || curr_year % 400 == 0) {
        return;
    } else {
        maxfeb = 27;
    }
    
    if (month_el.selectedIndex == 1 && day_el.selectedIndex > maxfeb) {
        day_el.selectedIndex = maxfeb;
    }
}
</script>
