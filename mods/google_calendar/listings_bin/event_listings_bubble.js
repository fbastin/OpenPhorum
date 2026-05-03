document.body.style.height = "100%";
var closetimer=0;
var curr_closetimer = "";

//get the actual height of the window
function phorum_mod_google_calendar_f_clientHeight() {
	return phorum_mod_google_calendar_f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
}
function phorum_mod_google_calendar_f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

//get the position of the read url to set the position of the event bubble
function phorum_mod_google_calendar_findPos(obj,event_bubble) {
	var curleft = curtop = 0;
	
    //find the offset of the read url
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}
	
	// set the event bubble below the read url by default
	curtop += 19;
	
	// find the actual potential position of the event bubble on the screen
	if (document.body.scrollTop) {
		actualheight = curtop - document.body.scrollTop;
	} else if (document.documentElement.scrollTop) {
		actualheight = curtop - document.documentElement.scrollTop
	} else if (window.pageYOffset) {
		actualheight = curtop - window.pageYOffset;
	} else {
		actualheight = curtop;
	}

	// find the maximum height to place the event bubble at which it will be 
    // fully visible
	windowheight = phorum_mod_google_calendar_f_clientHeight();
	maxtop = windowheight - event_bubble.clientHeight - 19;
	
	// if the event bubble will vertically fall outside the screen, put it above 
    // the url
	if (actualheight > maxtop) curtop = curtop - event_bubble.clientHeight - 22;
	
	// if the event bubble will horizontally fall outside the screen, pull it 
    // back in
	maxleft = document.body.clientWidth - event_bubble.clientWidth - 10;
	if (curleft > maxleft) curleft = maxleft;
	
	return [curleft,curtop];
	
}
//show the event bubble
function phorum_mod_google_calendar_show_event_bubble(curr_el) {
     
    curr_closetimer = curr_el.id;
    
    phorum_mod_google_calendar_cancel_closetimer(curr_el.id);
    
	event_node = curr_el;
    
	//grab the message id for this event
    message_id = curr_el.id.substr(curr_el.id.lastIndexOf("_")+1);
    
	event_bubble = document.getElementById("phorum_mod_google_calendar_event_bubble_div");
	
	// if this is an extra events bubble
    if (message_id.match("MED") == "MED") {
        //get the event bubble HTML
        event_bubble_HTML = phorum_mod_google_calendar_js_extra_event_data[message_id];
        // extra event bubbles need settings to allow scrolling and size control
        event_bubble.style.whiteSpace="nowrap";
        // allow scrolling if necessary
        event_bubble.style.overflow = "auto";
        // but not horizontal scrolling
        event_bubble.style.overflowX = "hidden";
        event_bubble.style.maxHeight = "100px";
        event_bubble.style.maxWidth = "500px";
        
    } else {
        //get the base event bubble HTML 
        event_bubble_HTML = phorum_mod_google_calendar_event_bubble_HTML;
        //get the event variables
        event_data = phorum_mod_google_calendar_js_event_data[message_id];
        //process the event variables in the base HTML
        for (key in event_data) {
            str = new RegExp("%"+key+"%","gi")
            event_bubble_HTML = event_bubble_HTML.replace(str, event_data[key]); 
        }
    }
    //assign the event bubble HTML
    event_bubble.innerHTML = event_bubble_HTML;
    //show the event bubble
    event_bubble.style.display = "block";
    
    //get the position of the selected event to set the position of the event bubble
	event_bubble_pos = phorum_mod_google_calendar_findPos(event_node,event_bubble);
    
    //position the event bubble
    event_bubble.style.left=event_bubble_pos[0]+"px";
    event_bubble.style.top=event_bubble_pos[1]+"px";
	
}

// set the timer to close the event bubble
function phorum_mod_google_calendar_set_closetimer(curr_id) {
    //only if the curr_id is empty or matches the current event id
    if (!curr_id || curr_id == curr_closetimer) {
        closetimer = window.setTimeout('phorum_mod_google_calendar_hide_event_bubble(),5');
    }
}
//cacel the timer in case we need to keep it open a little longer
function phorum_mod_google_calendar_cancel_closetimer(curr_id) { 
    //only if the curr_id is empty or matches the current event id
	if (closetimer && (!curr_id || curr_id == curr_closetimer)) {
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}
//hide the bubble div when closed
function phorum_mod_google_calendar_hide_event_bubble() {
	event_bubble = document.getElementById("phorum_mod_google_calendar_event_bubble_div");
	event_bubble.style.display = "none";
	event_bubble.innerHTML = "&nbsp;";
}

