function phorum_mod_google_calendar_show_event_listing() {
    //get the elements to be changed
    event_listing_hidden_div = document.getElementById("phorum_mod_google_calendar_event_listing_hidden_div");
    event_listing_shown_div = document.getElementById("phorum_mod_google_calendar_event_listing_shown_div");
    
    //show the event listing div
    event_listing_hidden_div.style.display = "none";
    event_listing_shown_div.style.display = "block";
    
    //check to see if the cookie to hide the listing has been set
    var theCookie = "" + document.cookie;
    var ind = theCookie.indexOf("phorum_mod_automatic_timezones_offset");
    //if the cookie exists, cause it to expire
    if (ind != -1) {
        var today = new Date();
        var expire = new Date();
        expire.setTime(today.getTime() - 3600);
        document.cookie = "phorum_mod_google_calendar_hide_event_listing=hide;expires="+expire.toGMTString();
    }

}

function phorum_mod_google_calendar_hide_event_listing() {
    //get the elements to be changed
    event_listing_hidden_div = document.getElementById("phorum_mod_google_calendar_event_listing_hidden_div");
    event_listing_shown_div = document.getElementById("phorum_mod_google_calendar_event_listing_shown_div");
    
    //hide the event listing div
    event_listing_hidden_div.style.display = "block";
    event_listing_shown_div.style.display = "none";
    
    //set the cookie to hide the listing
    var today = new Date();
    var expire = new Date();
    //reset the cookie as directed by the session timeout the admin has chosen
    expire.setTime(today.getTime() + phorum_mod_google_calendar_cookie_timeout);
    document.cookie = "phorum_mod_google_calendar_hide_event_listing=hide;expires="+expire.toGMTString();
        
}

