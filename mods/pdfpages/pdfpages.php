<?php
function phorum_mod_pdfpages_addon() {
    global $PHORUM;

    include_once("./include/email_functions.php");
    include_once("./include/format_functions.php");

    $thread = $PHORUM['args']['thread'];

    $PHORUM["threaded_read"]=0;

    $data = phorum_db_get_messages($thread,0);


    if(!empty($data) && isset($data[$thread])) {

        // setup the url-templates needed later
        $read_url_template   = phorum_get_url(PHORUM_READ_URL, '%thread_id%','%message_id%');


        if($PHORUM["max_attachments"]>0) {
            $attachment_download_url_template = phorum_get_url(PHORUM_FILE_URL, 'file=%file_id%', 'filename=%file_name%', 'download=1');
        }

        $fetch_user_ids = null;
        if (isset($data['users'])) {
            $fetch_user_ids = $data['users'];
            unset($data['users']);
        }

        $remove_threaded_bodies=0;
        $thread_is_closed = (bool)$data[$thread]["closed"];

        // fetch_user_ids filled from phorum_db_get_messages
        if(isset($fetch_user_ids) && count($fetch_user_ids)){
            $user_info=phorum_api_user_get($fetch_user_ids);
            // hook to modify user info
            if (isset($PHORUM["hooks"]["read_user_info"]))
            $user_info = phorum_hook("read_user_info", $user_info);
        }

        // main loop for template setup
        $messages=array();
        $read_messages=array(); // needed for newinfo
        foreach($data as $key => $row) {

            // assign user data to the row
            if($row["user_id"] && isset($user_info[$row["user_id"]])){
                if(is_numeric($user_info[$row["user_id"]]["date_added"])){
                    $user_info[$row["user_id"]]["raw_date_added"] = $user_info[$row["user_id"]]["date_added"];
                    $user_info[$row["user_id"]]["date_added"] = phorum_relative_date($user_info[$row["user_id"]]["date_added"]);
                }
                if(strlen($user_info[$row["user_id"]]["posts"])>3 && !strstr($user_info[$row["user_id"]]["posts"], $PHORUM["thous_sep"])){
                    $user_info[$row["user_id"]]["posts"] = number_format($user_info[$row["user_id"]]["posts"], 0, "", $PHORUM["thous_sep"]);
                }

                $row["user"]=$user_info[$row["user_id"]];
                unset($row["user"]["password"]);
                unset($row["user"]["password_tmp"]);
            }
            // is the message unapproved?
            $row["is_unapproved"] = ($row['status'] < 0) ? 1 : 0;

            // this stuff is used in threaded and non threaded.
            $row["raw_short_datestamp"] = $row["datestamp"];
            $row["short_datestamp"] = phorum_date($PHORUM["short_date_time"], $row["datestamp"]);
            $row["raw_datestamp"] = $row["datestamp"];
            $row["datestamp"] = phorum_date($PHORUM["long_date_time"], $row["datestamp"]);

            $row["URL"]["READ"]   = str_replace(array('%thread_id%','%message_id%'),array($row["thread"], $row["message_id"]),$read_url_template);


            // check if its the first message in the thread
            if($row["message_id"] == $row["thread"]) {
                $row["threadstart"] = true;
            } else{
                $row["threadstart"] = false;
            }

            // check if the default reply subject was used
            if($row["subject"] == "Re: ".$data[$thread]["subject"]){
                $row["default_reply"] = true;
            } else {
                $row["default_reply"] = false;
            }

            // should we show the signature?
            if(isset($row['body'])) {
                if(isset($row["user"]["signature"])
                && isset($row['meta']['show_signature']) && $row['meta']['show_signature']==1){

                    $phorum_sig=trim($row["user"]["signature"]);
                    if(!empty($phorum_sig)){
                        $row["body"].="\n\n$phorum_sig";
                    }
                }

                // add the edited-message to a post if its edited
                if(isset($row['meta']['edit_count']) && $row['meta']['edit_count'] > 0) {
                    $editmessage = str_replace ('%count%', $row['meta']['edit_count'], $PHORUM['DATA']['LANG']['EditedMessage']);
                    $editmessage = str_replace ('%lastedit%', phorum_date($PHORUM['short_date_time'],$row['meta']['edit_date']),  $editmessage);
                    // edit_username missing in older posts
                    $editmessage = str_replace('%lastuser%', (empty($row['meta']['edit_username']))?'(n/a)':$row['meta']['edit_username'], $editmessage);
                    $row['body'].="\n\n\n\n$editmessage";
                    if (      $row['meta']['edit_count'] > 0
                          && (     $PHORUM['track_edits'] == PHORUM_EDIT_TRACK_ON
                               || (    $PHORUM['track_edits'] == PHORUM_EDIT_TRACK_MODERATOR
                                    && isset($PHORUM['DATA']['MODERATOR'])
                                    && $PHORUM['DATA']['MODERATOR'] ) ) ) {
                        $row['URL']['CHANGES'] = str_replace('%message_id%',$row['message_id'],$changes_url_template);
                    }
                }
            }

            // mask host if not a moderator
            if(empty($PHORUM["user"]["admin"]) && (empty($PHORUM["DATA"]["MODERATOR"]) || !PHORUM_MOD_IP_VIEW)){
                if($PHORUM["display_ip_address"]){
                    if($row["moderator_post"]){
                        $row["ip"]=$PHORUM["DATA"]["LANG"]["Moderator"];
                    } elseif(is_numeric(str_replace(".", "", $row["ip"]))){
                        $row["ip"]=substr($row["ip"],0,strrpos($row["ip"],'.')).'.---';
                    } else {
                        $row["ip"]="---".strstr($row["ip"], ".");
                    }

                } else {
                    $row["ip"]="";
                }
            }

            if($PHORUM["max_attachments"]>0 && isset($row["meta"]["attachments"])){
                $row["attachments"]=$row["meta"]["attachments"];
                // unset($row["meta"]["attachments"]);
                foreach($row["attachments"] as $key=>$file){
                    $row["attachments"][$key]["size"] = phorum_filesize($file["size"]);
                    $row["attachments"][$key]["name"] = htmlspecialchars($file['name'], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
                    $safe_file = preg_replace('/[^\w\_\-\.]/', '_', $file['name']);
                    $safe_file = preg_replace('/_+/', '_', $safe_file);
                    $row["attachments"][$key]["download_url"]  = str_replace(array('%file_id%','%file_name%'),array($file['file_id'],$safe_file),$attachment_download_url_template);
                }
            }

            $messages[$row["message_id"]]=$row;
        }

        // run read mods
        if (isset($PHORUM["hooks"]["read"]))
        $messages = phorum_hook("read", $messages);

        // format messages
        $messages = phorum_format_messages($messages);

        // set up the data

        // this is the message that is the first in the thread
        $PHORUM["DATA"]["TOPIC"] = $messages[$thread];


        // this is all messages on the page
        $PHORUM["DATA"]["MESSAGES"] = $messages;

        // No htmlspecialchars() needed. The subject is already escaped.
        // Strip HTML tags from the HTML title. There might be HTML in
        // here, because of modules adding images and formatting.
        $PHORUM["DATA"]["HTML_TITLE"] = trim(strip_tags($PHORUM["threaded_read"] ? $PHORUM["DATA"]["MESSAGE"]["subject"] : $PHORUM["DATA"]["TOPIC"]["subject"]));

        $PHORUM["DATA"]["DESCRIPTION"] = htmlspecialchars(preg_replace('!\s+!s'," ",strip_tags(substr($PHORUM["DATA"]["TOPIC"]["body"],0,300))), ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);


    }

    require_once('./mods/pdfpages/tcpdf/tcpdf.php');

    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set default header data
    $pdf->SetHeaderData('', 0, $PHORUM["DATA"]["TITLE"]." / ". $PHORUM["DATA"]["NAME"] ,$PHORUM["DATA"]["HTML_TITLE"]);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('PDF '.$PHORUM["DATA"]["HTML_TITLE"]);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->AddPage();

    if (isset($PHORUM["DATA"]["MESSAGES"])) {
        foreach($PHORUM["DATA"]["MESSAGES"] as $message) {

            // add author and date as small fonts
            $pdf->SetFont('helvetica', '', 6);
            $pdf->writeHTML($message['author']." / ".$message['datestamp'],true);

            // add subject as a linked text
            $pdf->SetFont('helvetica', '', 10);
            $pdf->addHtmlLink($message["URL"]["READ"],$message['subject']);
            $pdf->Ln();

            // add the attachments as links
            $att = "";
            if(isset($message['attachments']) && count($message['attachments'])) {
                $att = "<br /><br />".$PHORUM['DATA']['LANG']['Attachments'].":<ul>";
                foreach($message['attachments'] as $attachment) {
                    $att.="<li><a href=\"{$attachment['download_url']}\">{$attachment['name']}</a></li>";
                }
                $att.="</ul>";
            }

            // remove all images in the body, it gives weird error messages otherwise
            // as it tries to load them from the local filesystem
            // a sane solution would require to download/export the images before getting
            // to this point and replacing the img-links in the body with the local
            // version(s)
            $body = preg_replace('!</?img((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>!', '',$message['body']);

            // output the HTML body
            $pdf->writeHTML("<br />".$body."$att<br /><hr />", true, 0, true, 0);
        }
    }

    // reset pointer to the last page
    $pdf->lastPage();

    //Close and output PDF document
    $pdf->Output("forumthread_{$thread}.pdf","D");
}

function phorum_mod_pdfpages_read($data) {

    global $PHORUM;

    $entry = current($data);
    // replace the printview link with our module link
    $PHORUM['DATA']['URL']['PRINTVIEW']=phorum_get_url(PHORUM_ADDON_URL,'module=pdfpages','thread='.$entry['thread']);

    return $data;
}
?>