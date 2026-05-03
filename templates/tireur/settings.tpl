{! --- defines are used by the engine and vars are used by the template --- }

{! --- How many px to indent for each level --- }
{DEFINE indentmultiplier 20}

{! --- This is used to load the message-bodies in the message-list for that template if set to 1 --- }
{DEFINE bodies_in_list 0}

{! --- This is used the number of page numbers shown on the list page in the paging section (eg. 1 2 3 4 5) --- }
{DEFINE list_pages_shown 5}

{! --- Define on what pages notifications should be displayed ---- }
{DEFINE show_notify_for_pages "index,list,cc"}

{! --- Apply some compression to the template data. This feature is      --- }
{! --- implemented by Phorum's template parsing code. Possible values    --- }
{! --- for this setting are:                                             --- }
{! --- 0 - Apply no compression at all.                                  --- }
{! --- 1 - Remove white space at start of lines and empty lines.         --- }
{! --- 2 - Additionally, remove some extra unneeded white space and HTML --- }
{!         comments. Note that this makes the output quite unreadable,   --- }
{!         so it is mainly useful for a production environment.          --- }
{DEFINE tidy_template 0}

{! -- This is the image for the gauge bar to show how full the PM box is -- }
{VAR gauge_image "templates/emerald/images/gauge.gif"}

{! -- Fonts -- }

{VAR default_font "Verdana, sans-serif"}

{VAR font_xx_large  "12pt"}
{VAR font_x_large   "11pt"}
{VAR font_large     "10pt"}
{VAR base_font_size "9pt"}
{VAR font_small     "8pt"}
{VAR font_x_small   "7pt"}
{VAR font_xx_small  "7pt"}

{! -- The maximum width of the Phorum content (the div with id "phorum")  -- }
{VAR max_width "100%"}  {! -- CSS size values allowed. No effect in MSIE 6 }
{VAR max_width_ie "100%"} {! -- px width values allowed. Sets max MSIE 6 width }

{! -- Logo size (images/logo.png). Update this if you replace the logo.png -- }
{VAR logo_width     "468"}
{VAR logo_height    "60"}

{! -- colors -- }
{VAR body_background_color "black"}
{VAR default_font_color "Black"}
{VAR default_background_color "White"}
{VAR gen_background_color "#ffffdc"} {! -- should compliment default_background_color -- }
{!-- E8E7F9}
{VAR alt_background_color "#e8e7f9"} {! -- should compliment default_background_color -- }
{VAR highlight_background_color "#CFCDF3"} {! -- should compliment the two above -- }
{VAR th_background_color "#2e2e45"}        {! not a standard setting }
{VAR border_color "#352FA7"}
{VAR border_font_color "White"}
{VAR quote_border_color "#808080"}
{VAR pre_border_color "#C4C6A2"}
{VAR pre_background_color "#FEFFEC"}
{VAR link_color "#2E2E45"}
{VAR link_hover_color "#709CCC"}
{VAR new_color "red"}
{VAR logo_background_color "#ffffff"}
{VAR breadcrumb_border_color "#b6b6b6"}
{VAR post_moderation_background_color "#fffdf6"}
{VAR information_border_color "#6262a7"}
{VAR information_background_color "#ffffc0"}
{VAR warning_border_color "#A76262"}
{VAR warning_background_color "#FFD1D1"}
{VAR span_addition_background_color "#CBCBFF"}
{VAR span_addition_font_color "#000000"}
{VAR span_removal_background_color "#FFFFCB"}
{VAR span_removal_font_color "#000000"}
{VAR message_background_color "#fafaff"}

{! -- Background Images -- }
{VAR header_background_image ""}
{VAR top_background_image ""}
{VAR message_background_image ""}

