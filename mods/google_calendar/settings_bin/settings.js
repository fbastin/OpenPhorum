
var need_to_confirm = false;
var deleted_need_to_confirm = false;
var global_new_row_num = 0;
var global_onchange_data = new Array();
var global_valid_data_name = new Array();
var global_valid_data_empty_required = new Array();
var global_curr_calendar = false;
var global_curr_color = "";
var global_color_chooser_clicked = 0;

//setup a flag if a form field has been changed
function flag_unsaved_changes() {
    need_to_confirm = true;
    window.onbeforeunload = confirm_unsaved_changes;
}
//setup a flag if a form field has been changed for the deleted form
function flag_unsaved_deleted_changes() {
    deleted_need_to_confirm = true;
    window.onbeforeunload = confirm_unsaved_changes;
}

//warn the user if they are navigating away from a form that has changed fields
function confirm_unsaved_changes() {
    if (need_to_confirm) {
        return 'You have not saved the changes you made.';
    } else if (deleted_need_to_confirm) {
        return 'You have not submitted the restorations or deletions you made.';
    }
}

//confirm the desire to save the changed settings
function confirm_submit() {
    for (i=0; i<= global_valid_data_name.length; i++) {
        if (global_valid_data_name[i] == 0) {
            alert ("One or more of your calendar names contains illegal characters.");
            return false;
        }
    }
    for (i=0; i<= global_valid_data_empty_required.length; i++) {
        if (global_valid_data_empty_required[i] == 0) {
            if (document.getElementById('calendar_empty_required_'+i).value == "") {
                alert ("If you have marked a calendar as required, you must enter an error message to display if the calendar is left empty.");
                return false;
            }
        }
    }
    confirmation = confirm('Are you sure you want to save these changes?');
    if (confirmation) need_to_confirm = false;
    return confirmation;
}

//allow changing the calendar type
function unlock_calendar_type(curr_el) {
    alert("You cannot change the way you color-code events until you have deleted (though not necessarily fully deleted) every "+global_type_name+".");
    //curr_el.style.display = "none";
    //document.getElementById("calendar_type_id").disabled = false;
    //document.getElementById("hidden_calendar_type_id").disabled = true;
}
//confirm a change in calendar types
function submit_calendar_type(curr_el) {
    confirmation = confirm('Are you sure you want to change your color-coding scheme?');
    if (confirmation) {
        location.href="./admin.php?module=modsettings&mod=google_calendar&phorum_admin_token=" + phorum_admin_token + "&cal_type=" + curr_el.options[curr_el.selectedIndex].value;
    } else {
        curr_el.selectedIndex = calendar_type_index;
    }
}

//verify the attempted category name
function check_name(name_text) {
    error_alert = "";
    ok_to_save = true;
    if (!name_text) {
        error_alert = "If a "+global_type_name+" name is left empty, changes to the empty current "+global_type_name+" will not be saved and any empty new "+global_type_name+" will not be created.";
    } else {/*
        possible_first_chars = "abcdefghijklmnopqrstuvwxyz";
        possible_chars = possible_first_chars + "0123456789_ ";
        for (i=0; i <= name_text.length; i++) {
            check_char = name_text.charAt(i);
            if (i == 0 && possible_first_chars.indexOf(check_char.toLowerCase()) == -1) {
                error_alert = "Sorry, "+global_type_name+" names must start with a letter.";
                ok_to_save = false;
                break;
            } else if (possible_chars.indexOf(check_char.toLowerCase()) == -1) {
                error_alert = "Sorry, "+global_type_name+" names can only contain letters, numbers, and underscores (_).";
                ok_to_save = false;
                break;
            }
        }*/
    }
    if (error_alert != "") alert(error_alert);

    return ok_to_save;
}

//verify that a forum is only selected once
function check_forum_id(forum_id) {
    error_alert = "";
    ok_to_save = true;
    
    for (i=0; i< global_new_row_num; i++) {
        if (document.getElementById("calendar_id_id_"+i).value == forum_id) {
            error_alert = "You have already chosen a color for that "+global_type_name+".";
            ok_to_save = false;
            break;
        }
    }
    
    if (error_alert != "") alert(error_alert);

    return ok_to_save;
}

//save changes made to form fields to global variables
function save_onchange_data(curr_el) {
    valid_data = true;
    row_id = curr_el.id.substr(curr_el.id.lastIndexOf("_")+1);
    if (!global_onchange_data[row_id]) {
        global_onchange_data[row_id] = new Object;
    }
    switch(curr_el.id) {
        case "calendar_name_"+row_id:
            if (global_type_name == "category") {
                valid_data = check_name(curr_el.value);
            } else {
                valid_data = check_forum_id(forum_ids_by_name[curr_el.value]);
                //if the forum has already been chosen, it cannot be selected
                if (!valid_data) {
                    reset_value = 0;
                    if (global_onchange_data[row_id][curr_el.id]) {
                        reset_value = global_onchange_data[row_id][curr_el.id];
                    } else {
                        reset_id = document.getElementById("calendar_id_id_"+row_id).value;
                        for (forum_name in forum_ids_by_name) {
                            if (forum_ids_by_name[forum_name] == reset_id) {
                                reset_value = forum_name;
                                break;
                            }
                        }
                    }
                    if (reset_value == 0) {
                        curr_el.selectedIndex = 0;
                    } else {
                        for (x in curr_el.options) {
                            if (curr_el.options[x].value == reset_value) {
                                curr_el.selectedIndex = x;
                                break;
                            }
                        }
                    }
                    break;
                }
                document.getElementById("calendar_id_id_"+row_id).value = forum_ids_by_name[curr_el.value];
                global_onchange_data[row_id]["calendar_id_id_"+row_id] = forum_ids_by_name[curr_el.value];
            }
            if (!valid_data) {
                global_valid_data_name[row_id] = 0;
            } else {
                global_valid_data_name[row_id] = 1;
            }
            global_onchange_data[row_id][curr_el.id] = curr_el.value;
            break;
        case "calendar_type_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.selectedIndex;
            break;
        case "html_disabled_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.checked;
            break;
        case "show_in_admin_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.checked;
            break;
        case "calendar_dropdown_choices_"+row_id:
            curr_el.value = curr_el.value.replace(/, /g,",");
            global_onchange_data[row_id][curr_el.id] = curr_el.value;
            break;
        case "calendar_title_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.value;
            break;
        case "show_in_registration_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.checked;
            break;
        case "calendar_required_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.checked;
            if (curr_el.checked) {
                document.getElementById('calendar_empty_required_div_'+row_id).style.display="inline";
                if (document.getElementById('calendar_empty_required_'+row_id).value == "") global_valid_data_empty_required[row_id] = 0;
            } else {
                global_valid_data_empty_required[row_id] = 1;
                document.getElementById('calendar_empty_required_div_'+row_id).style.display="none";
            }
            break;
        case "calendar_empty_required_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.value;
            if (curr_el.value.length > 0) {
                global_valid_data_empty_required[row_id] = 1;
            } else {
                global_valid_data_empty_required[row_id] = 0;
            }
            break;
        case "calendar_html_"+row_id:
            global_onchange_data[row_id][curr_el.id] = curr_el.value;
            break;
    }
    flag_unsaved_changes();
}

//process a click on either the restore or fully delete fields
function deleted_onchange_data(curr_el) {
    row_id = curr_el.id.substr(curr_el.id.lastIndexOf("_")+1);
    if (curr_el.id == "restore_"+row_id) {
        other_el = document.getElementById("fully_delete_"+row_id);
    } else {
        other_el = document.getElementById("restore_"+row_id);
        if (curr_el.checked) check_delete = confirm("Are you sure you want to fully delete this calendar?");
        if (!check_delete) curr_el.checked = false;
    }
    if (curr_el.checked) {
        other_el.checked = false;
        flag_unsaved_deleted_changes();
    }
}

//confirm the choice to fully delete a calendar
function deleted_confirm_submit() {
    confirmation = confirm('Are you sure you want to save these changes?');
    if (confirmation) deleted_need_to_confirm = false;
    return confirmation;
}

//edit the chosen form field
function enable_edit(link,curr_id,whichedit) {
    if (whichedit == "type_dropdown") { 
        if (link.value != "html") {
            if (document.getElementById('calendar_custom_html_edit_'+curr_id)) document.getElementById('calendar_custom_html_edit_'+curr_id).style.display="none";
            document.getElementById('calendar_title_div_'+curr_id).style.display="inline";
            document.getElementById('calendar_dropdown_choices_div_'+curr_id).style.display="none";
            document.getElementById('calendar_required_div_'+curr_id).style.display="none";
            document.getElementById('show_in_admin_check_'+curr_id).style.display="inline";
            document.getElementById('show_in_admin_na_'+curr_id).style.display="none";
            global_valid_data_empty_required[curr_id] = 1;
            if (link.value == "input" || link.value == "textarea") {
                document.getElementById('html_disabled_na_'+curr_id).style.display="none";    
                document.getElementById('html_disabled_check_'+curr_id).style.display="inline";
                document.getElementById('calendar_required_div_'+curr_id).style.display="inline";
                if (document.getElementById('calendar_required_'+curr_id).checked) global_valid_data_empty_required[curr_id] = 0;
            } else if (link.value == "dropdown" || link.value == "dropdown_multi") {
                document.getElementById('calendar_dropdown_choices_div_'+curr_id).style.display="inline";
            } else {
                document.getElementById('html_disabled_check_'+curr_id).style.display="none";
                document.getElementById('html_disabled_na_'+curr_id).style.display="inline";
            }
        } else {
            document.getElementById('calendar_title_div_'+curr_id).style.display="none";
            document.getElementById('calendar_custom_html_edit_'+curr_id).style.display="inline";
            document.getElementById('html_disabled_check_'+curr_id).style.display="none";
            document.getElementById('html_disabled_na_'+curr_id).style.display="inline";
            document.getElementById('show_in_admin_check_'+curr_id).style.display="none";
            document.getElementById('show_in_admin_na_'+curr_id).style.display="inline";
        }
        save_onchange_data(link);
    //open the color chooser when editing the color field
    } else if (whichedit == "color") {
        curr_color = document.getElementById('hidden_calendar_color_'+curr_id).value;
        if (global_curr_color != "" && global_curr_color != curr_color) {
            pre_chosen_color = document.getElementById(global_curr_color);
            pre_chosen_color.innerHTML = "<img src='./images/trans.gif' alt='' border='0' width='10px' height='10px' />";
        }
        global_curr_calendar = curr_id;
        var display_pos = findPos(link, 24,40,90);
        color_chooser = document.getElementById('colordiv');
        global_curr_color = curr_color;
        chosen_color = document.getElementById(curr_color);
        chosen_color.innerHTML = "<img src=\"./mods/google_calendar/images/checkmark.png\"/>";
        global_color_chooser_clicked = 1;
        color_chooser.style.display = "block";
        color_chooser.style.left=display_pos[0]+"px";
        color_chooser.style.top=display_pos[1]+"px";
    //otherwise, open the selected field
    } else {
        document.getElementById('calendar_'+whichedit+'_display_'+curr_id).style.display="none";
        document.getElementById('calendar_'+whichedit+'_edit_'+curr_id).style.display="inline";
        flag_unsaved_changes();
    }
}

//choose a color, populate the form field, and close the color chooser.
function choose_color(curr_el) {
    color_chooser = document.getElementById('colordiv');
    curr_color = document.getElementById('hidden_calendar_color_'+global_curr_calendar).value;
    curr_chosen_color = document.getElementById(curr_color);
    curr_chosen_color.innerHTML = "<img src='./images/trans.gif' alt='' border='0' width='10px' height='10px' />";
    global_curr_color = "";
    color_chooser.style.display = "none";
    document.getElementById('hidden_calendar_color_'+global_curr_calendar).value = curr_el.id;
    link = document.getElementById('calendar_color_display_'+global_curr_calendar);
    link.style.backgroundColor = curr_el.id;
    flag_unsaved_changes();
}

//get the actual height of the window
function f_clientHeight() {
    return f_filterResults (
        window.innerHeight ? window.innerHeight : 0,
        document.documentElement ? document.documentElement.clientHeight : 0,
        document.body ? document.body.clientHeight : 0
    );
}

//return various height coordinates
function f_filterResults(n_win, n_docel, n_body) {
    var n_result = n_win ? n_win : 0;
    if (n_docel && (!n_result || (n_result > n_docel)))
        n_result = n_docel;
    return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

//get the position of the current element to set the position of the display div
function findPos(obj, set_below, display_div_height, display_div_width) {
    var curleft = curtop = 0;
    
    //find the offset of the current element
    if (obj.offsetParent) {
        do {
            curleft += obj.offsetLeft;
            curtop += obj.offsetTop;
        } while (obj = obj.offsetParent);
    }
    
    // set the display div below the current element by default
    curtop += set_below;//24;
    
    // find the actual potential position of the display div on the screen
    if (document.body.scrollTop) {
        actualheight = curtop - document.body.scrollTop;
    } else if (document.documentElement.scrollTop) {
        actualheight = curtop - document.documentElement.scrollTop
    } else if (window.pageYOffset) {
        actualheight = curtop - window.pageYOffset;
    } else {
        actualheight = curtop;
    }

    // find the maximum height to place the display div at which it will be fully visible
    windowheight = f_clientHeight();
    maxtop = windowheight - display_div_height - set_below;
    
    //if the display div will vertically fall outside the screen, put it above the current element
    if (actualheight > maxtop) curtop = curtop - display_div_height - (set_below * 2.6);
    
    //if the display div will horizontally fall outside the screen, pull it back in
    maxleft = document.body.clientWidth - display_div_width - 20;
    if (curleft > maxleft) curleft = maxleft;
    
    return [curleft,curtop];
    
}

//add a new calendar row
function add_new_calendar(add_button,new_row_num) {
    if (global_new_row_num == 0) global_new_row_num = new_row_num;
    
    calendar_form_table = document.getElementById('maintable');
    
    firstcol = document.createElement('td');
    firstcol_html = document.getElementById('hidden_new_col_1').innerHTML;
    firstcol_html = firstcol_html.replace(/new_row_num/g,global_new_row_num);
    firstcol.innerHTML = firstcol_html;
    
    secondcol = document.createElement('th');
    secondcol_html = document.getElementById('hidden_new_col_2').innerHTML;
    secondcol_html = secondcol_html.replace(/new_row_num/g,global_new_row_num);
    secondcol.innerHTML = secondcol_html;
    
    thirdcol = document.createElement('td');
    thirdcol_html = document.getElementById('hidden_new_col_3').innerHTML;
    thirdcol_html = thirdcol_html.replace(/new_row_num/g,global_new_row_num);
    thirdcol.innerHTML = thirdcol_html;
    
    lastrow = global_new_row_num+global_fixed_row_count;
    calendar_form_table.insertRow(lastrow);
    calendar_form_table.rows[lastrow].id = global_new_row_num;
    calendar_form_table.rows[lastrow].appendChild(firstcol);
    calendar_form_table.rows[lastrow].appendChild(secondcol);
    calendar_form_table.rows[lastrow].appendChild(thirdcol);
    firstcol.className='input-form-td';
    secondcol.className='input-form-th';
    thirdcol.className='input-form-td';
    thirdcol.style.textAlign = 'center';
    
    y=document.getElementById('calendar_id_id_'+global_new_row_num);
    y.focus();
    
    global_new_row_num = global_new_row_num+1;
}

function toggle_events_from_any_forum(curr_el) {
    if (curr_el.selectedIndex == 1) {
        document.getElementById('events_from_any_forum_question').style.display="none";
        document.getElementById('events_from_any_forum_answer').style.display="none";
        document.getElementById('events_from_selected_forums_question').style.display="block";
        document.getElementById('events_from_selected_forums_answer').style.display="block";
    }
}
// find out which element was clicked
function clickedOutsideElement(elemId, evt) {
    var theElem = '';

    if (window.event) {
        theElem = getEventTarget(window.event);
    } else {
        theElem = getEventTarget(evt);
    }

    while(theElem != null) {
        if(theElem.id == elemId) return false;
        theElem = theElem.offsetParent;
    }
    
    return true;
}

// find the event target
function getEventTarget(evt) {
    var targ = (evt.target) ? evt.target : evt.srcElement;
    
    if(targ != null) {
        if(targ.nodeType == 3) targ = targ.parentNode;
    }
    
    return targ;
}

//When clicking away from the color chooser, we need to hide the color chooser
//if we can get by with an event listener, do so
if (document.addEventListener) {
    document.addEventListener('click',global_onclick,true);

//otherwise follow the more complicated onclick route
} else {
    document.onclick = ie_global_onclick;
}

//if we had to go with the onclick route, find out if the click was outside the 
//color chooser and close it if necessary
function ie_global_onclick() {
    if (clickedOutsideElement('colordiv', window.event)) {
        if (global_color_chooser_clicked == 1) {
            global_color_chooser_clicked = 0;
        } else {
            global_onclick();
        }
    }
}

//close and reset the color chooser
function global_onclick() {
    if (global_curr_calendar === false) return;
    curr_color = document.getElementById('hidden_calendar_color_'+global_curr_calendar).value;
    curr_chosen_color = document.getElementById(curr_color);
    curr_chosen_color.innerHTML = "<img src='./images/trans.gif' alt='' border='0' width='10px' height='10px' />";
    global_curr_color = "";
    color_chooser.style.display = "none";
}

//create help fields
var help = Array;
    help[1] = ["Color-coded event grouping", "The module defaults to grouping events by the forum in which they are posted.  There are two other options for event groupings.  You can group events by the folder in which they are posted.  You can also group events into custom categories you supply.  If you choose to group events by categories, a category dropdown will be placed in the event creation form to allow the post author to choose the category in which to post the event."];
    help[2] = ["Embedding the Google calendar", "This module supports embedding your Google calendar into Phorum.  By doing so, a page is created which shows your calendar in Google's week, month, or agenda view.  Please refer to the <a target=\"_blank\" href=\""+full_help_doc_url+"\">Installation and Usage Guide</a> for more information."];
    help[3] = ["Show event data in the message body", "This will place the event start and end times and the event location, if it is given, at the top of the message in which the event was created."];
    help[4] = ["Show a seven day event listing", "You can show an event listing on selected pages.  This listing will show seven days worth of color-coded events. Each event can be clicked which will then popup more information about the event and a link to the post in which the was created.<br/>To enable this listing you must first choose which type of listing you would like to show. Your choices are:<ul><li>Do not show an event listing</li><li>Show a listing of the current week's events</li><li>Show a listing of the next seven day's events (which starts with events occurring on the current day and events from the six following days.</li></ul>"];
    help[5] = ["Show event listing automatically", "You can choose to show the event listing automatically below the page header.  If you do not select this choice, you can instead show the event listing anywhere on a page by editing the relevant template files to add the line {INCLUDE \"google_calendar::event_listing\"} wherever you would like to display the listing."];
    help[6] = ["Maximum number of events to show in a single day", "You can limit the number of events which will show in a single day. If there are more events in that day then the number allowed, a link will be show at the bottom of that day (e.g. \"+3 more\") which, when clicked, will show a popup listing of the extra events. The default is to limit the number of events shown to 5 for a single day."];
    help[7] = ["Color-Coding Name", ""];
    help[8] = ["Color", "Click on the color for your forum, folder, or category and a pop up will appear from which you can select one of the preset colors Google allows."];
    help[9] = ["Delete", "Please note that this deletion is not permanent.  You are simply moving the color-coding to the deleted list and can still restore that color-coding later.  When you delete a color-coding, the Google calendar associated with it will be hidden, no current events will be shown, and no new events can be added."];
    help[10] = ["Restore", "When you restore a color-coding, the Google calendar associated with it will be made visible, current events will be shown again, and new events can be added."];
    help[11] = ["Fully Delete", "When you permanently delete a color-coding, the Google calendar associated with is deleted, no current events will be shown, and no new events can be added. You will be asked to confirm this decision."];
    help[12] = ["Selected groups can post events", "You can allow one or more groups to post calendar events.  Ctrl-click to select multiple events."];

